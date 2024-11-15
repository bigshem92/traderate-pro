<?php
// app/Services/PaymentService.php
namespace App\Services;

use Stripe\Stripe;
use App\Models\Quote;
use App\Models\Transaction;

class PaymentService {
    public function __construct() {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    }

    public function processPayment($quoteId, $paymentMethod) {
        try {
            $quote = Quote::find($quoteId);
            $amount = $quote->amount * 100; // Convert to cents

            $payment = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'gbp',
                'payment_method' => $paymentMethod,
                'confirm' => true,
                'metadata' => [
                    'quote_id' => $quoteId,
                    'job_id' => $quote->job_id
                ]
            ]);

            if ($payment->status === 'succeeded') {
                $this->createTransaction($quote, $payment);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            throw new \Exception('Payment failed: ' . $e->getMessage());
        }
    }

    private function createTransaction($quote, $payment) {
        return Transaction::create([
            'quote_id' => $quote->id,
            'amount' => $quote->amount,
            'payment_id' => $payment->id,
            'status' => 'completed'
        ]);
    }
}

// app/Services/NotificationService.php
namespace App\Services;

use App\Models\Notification;
use PHPMailer\PHPMailer\PHPMailer;

class NotificationService {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->setupMailer();
    }

    private function setupMailer() {
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['MAIL_HOST'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['MAIL_USERNAME'];
        $this->mailer->Password = $_ENV['MAIL_PASSWORD'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $_ENV['MAIL_PORT'];
    }

    public function notifyNewQuote($quote) {
        $job = $quote->job();
        $trader = $quote->trader();
        $customer = $job->customer();

        $notification = Notification::create([
            'user_id' => $customer->id,
            'type' => 'new_quote',
            'data' => json_encode([
                'quote_id' => $quote->id,
                'job_id' => $job->id,
                'trader_name' => $trader->business_name
            ])
        ]);

        $this->sendEmail(
            $customer->email,
            'New Quote Received',
            'emails/new-quote',
            ['quote' => $quote, 'job' => $job, 'trader' => $trader]
        );
    }

    private function sendEmail($to, $subject, $template, $data) {
        try {
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $this->renderEmailTemplate($template, $data);
            $this->mailer->send();
        } catch (\Exception $e) {
            // Log error but don't stop execution
            error_log('Email sending failed: ' . $e->getMessage());
        }
    }
}

// app/Services/FileUploadService.php
namespace App\Services;

use Intervention\Image\ImageManager;

class FileUploadService {
    private $imageManager;
    private $uploadPath;

    public function __construct() {
        $this->imageManager = new ImageManager();
        $this->uploadPath = $_ENV['UPLOAD_PATH'];
    }

    public function uploadImage($file, $directory, $resize = true) {
        try {
            $filename = uniqid() . '_' . $file['name'];
            $path = $this->uploadPath . '/' . $directory . '/' . $filename;

            if ($resize) {
                $image = $this->imageManager->make($file['tmp_name']);
                $image->resize(800, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $image->save($path);
            } else {
                move_uploaded_file($file['tmp_name'], $path);
            }

            return $filename;
        } catch (\Exception $e) {
            throw new \Exception('File upload failed: ' . $e->getMessage());
        }
    }

    public function deleteFile($filename, $directory) {
        $path = $this->uploadPath . '/' . $directory . '/' . $filename;
        if (file_exists($path)) {
            unlink($path);
        }
    }
}

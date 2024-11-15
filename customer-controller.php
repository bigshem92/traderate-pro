<?php
namespace App\Controllers;

use App\Services\CustomerService;
use App\Services\JobService;

class CustomerController extends BaseController {
    private $customerService;
    private $jobService;

    public function __construct() {
        $this->requireAuth();
        $this->customerService = new CustomerService();
        $this->jobService = new JobService();
    }

    public function dashboard() {
        $activeJobs = $this->jobService->getCustomerJobs($_SESSION['user_id'], 'active');
        $completedJobs = $this->jobService->getCustomerJobs($_SESSION['user_id'], 'completed');
        
        return $this->view('customer/dashboard', [
            'activeJobs' => $activeJobs,
            'completedJobs' => $completedJobs
        ]);
    }

    public function postJob() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validate($_POST, [
                'title' => 'required',
                'description' => 'required',
                'category_id' => 'required',
                'postcode' => 'required',
                'budget_range' => 'required'
            ]);

            if (empty($errors)) {
                try {
                    // Handle file uploads if any
                    $files = $this->handleFileUploads($_FILES['images'] ?? []);
                    $jobData = array_merge($_POST, ['images' => $files]);
                    
                    $job = $this->jobService->createJob($_SESSION['user_id'], $jobData);
                    return $this->redirect('/customer/jobs/' . $job->id);
                } catch (\Exception $e) {
                    $errors['job'] = [$e->getMessage()];
                }
            }
            
            return $this->view('customer/post-job', ['errors' => $errors]);
        }

        return $this->view('customer/post-job');
    }

    public function viewQuotes($jobId) {
        $job = $this->jobService->getJob($jobId);
        if ($job->customer_id !== $_SESSION['user_id']) {
            return $this->redirect('/customer/dashboard');
        }

        $quotes = $this->jobService->getJobQuotes($jobId);
        return $this->view('customer/quotes', [
            'job' => $job,
            'quotes' => $quotes
        ]);
    }

    public function acceptQuote($quoteId) {
        try {
            $quote = $this->jobService->acceptQuote($quoteId, $_SESSION['user_id']);
            return $this->json(['success' => true, 'quote' => $quote]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    private function handleFileUploads($files) {
        $uploadedFiles = [];
        foreach ($files['tmp_name'] as $key => $tmp_name) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $filename = uniqid() . '_' . $files['name'][$key];
                $path = UPLOAD_PATH . '/jobs/' . $filename;
                
                if (move_uploaded_file($tmp_name, $path)) {
                    $uploadedFiles[] = $filename;
                }
            }
        }
        return $uploadedFiles;
    }
}

// app/Controllers/TraderController.php
<?php
namespace App\Controllers;

use App\Services\TraderService;
use App\Services\ReviewService;
use App\Services\JobService;

class TraderController extends BaseController {
    private $traderService;
    private $reviewService;
    private $jobService;

    public function __construct() {
        $this->requireAuth();
        $this->traderService = new TraderService();
        $this->reviewService = new ReviewService();
        $this->jobService = new JobService();
    }

    public function dashboard() {
        $trader = $this->traderService->getTraderProfile($_SESSION['user_id']);
        $recentJobs = $this->jobService->getRecentJobs($trader->id);
        $stats = $this->traderService->getStats($trader->id);
        
        return $this->view('trader/dashboard', [
            'trader' => $trader,
            'recentJobs' => $recentJobs,
            'stats' => $stats
        ]);
    }

    public function profile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validate($_POST, [
                'business_name' => 'required',
                'description' => 'required',
                'service_areas' => 'required'
            ]);

            if (empty($errors)) {
                try {
                    $trader = $this->traderService->updateProfile($_SESSION['user_id'], $_POST);
                    return $this->json(['success' => true, 'trader' => $trader]);
                } catch (\Exception $e) {
                    return $this->json(['error' => $e->getMessage()], 400);
                }
            }
            
            return $this->json(['errors' => $errors], 400);
        }

        $trader = $this->traderService->getTraderProfile($_SESSION['user_id']);
        return $this->view('trader/profile', ['trader' => $trader]);
    }

    public function jobs() {
        $status = $_GET['status'] ?? 'active';
        $jobs = $this->jobService->getTraderJobs($_SESSION['user_id'], $status);
        
        return $this->view('trader/jobs', ['jobs' => $jobs]);
    }

    public function reviews() {
        $reviews = $this->reviewService->getTraderReviews($_SESSION['user_id']);
        $stats = $this->reviewService->getReviewStats($_SESSION['user_id']);
        
        return $this->view('trader/reviews', [
            'reviews' => $reviews,
            'stats' => $stats
        ]);
    }

    public function submitQuote($jobId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validate($_POST, [
                'amount' => 'required',
                'description' => 'required',
                'estimated_days' => 'required'
            ]);

            if (empty($errors)) {
                try {
                    $quote = $this->jobService->submitQuote($jobId, $_SESSION['user_id'], $_POST);
                    return $this->json(['success' => true, 'quote' => $quote]);
                } catch (\Exception $e) {
                    return $this->json(['error' => $e->getMessage()], 400);
                }
            }
            
            return $this->json(['errors' => $errors], 400);
        }
    }
}

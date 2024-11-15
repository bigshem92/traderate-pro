<?php
namespace App\Controllers;

use App\Services\JobService;
use App\Services\TraderService;

class JobController extends BaseController {
    private $jobService;
    private $traderService;

    public function __construct() {
        $this->jobService = new JobService();
        $this->traderService = new TraderService();
    }

    public function index() {
        $filters = [
            'category' => $_GET['category'] ?? null,
            'location' => $_GET['location'] ?? null,
            'budget_max' => $_GET['budget_max'] ?? null,
            'status' => $_GET['status'] ?? 'open'
        ];

        $jobs = $this->jobService->searchJobs($filters);
        return $this->view('jobs/index', ['jobs' => $jobs]);
    }

    public function view($id) {
        $job = $this->jobService->getJob($id);
        if (!$job) {
            return $this->redirect('/jobs');
        }

        $isOwner = $this->isAuthenticated() && $_SESSION['user_id'] === $job->customer_id;
        $canQuote = $this->isAuthenticated() && $_SESSION['user_type'] === 'trader';

        return $this->view('jobs/view', [
            'job' => $job,
            'isOwner' => $isOwner,
            'canQuote' => $canQuote
        ]);
    }

    public function update($id) {
        $this->requireAuth();
        $job = $this->jobService->getJob($id);
        
        if (!$job || $job->customer_id !== $_SESSION['user_id']) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validate($_POST, [
                'title' => 'required',
                'description' => 'required',
                'status' => 'required'
            ]);

            if (empty($errors)) {
                try {
                    $updatedJob = $this->jobService->updateJob($id, $_POST);
                    return $this->json(['success' => true, 'job' => $updatedJob]);
                } catch (\Exception $e) {
                    return $this->json(['error' => $e->getMessage()], 400);
                }
            }
            
            return $this->json(['errors' => $errors], 400);
        }
    }

    public function complete($id) {
        $this->requireAuth();
        
        try {
            $job = $this->jobService->completeJob($id, $_SESSION['user_id']);
            return $this->json(['success' => true, 'job' => $job]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function cancel($id) {
        $this->requireAuth();
        
        try {
            $job = $this->jobService->cancelJob($id, $_SESSION['user_id']);
            return $this->json(['success' => true, 'job' => $job]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}

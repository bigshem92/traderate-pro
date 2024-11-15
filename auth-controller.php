// app/Controllers/AuthController.php
<?php
namespace App\Controllers;

use App\Services\AuthService;
use App\Services\UserService;

class AuthController extends BaseController {
    private $authService;
    private $userService;

    public function __construct() {
        $this->authService = new AuthService();
        $this->userService = new UserService();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validate($_POST, [
                'email' => 'required|email',
                'password' => 'required|min:8'
            ]);

            if (empty($errors)) {
                try {
                    $user = $this->authService->authenticate($_POST['email'], $_POST['password']);
                    $_SESSION['user_id'] = $user->id;
                    $_SESSION['user_type'] = $user->type;
                    
                    $this->redirect('/dashboard');
                } catch (\Exception $e) {
                    $errors['auth'] = [$e->getMessage()];
                }
            }
            
            return $this->view('auth/login', ['errors' => $errors]);
        }

        return $this->view('auth/login');
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validate($_POST, [
                'email' => 'required|email',
                'password' => 'required|min:8',
                'name' => 'required',
                'user_type' => 'required',
                'terms' => 'required'
            ]);

            if (empty($errors)) {
                try {
                    $user = $this->userService->createUser($_POST);
                    $_SESSION['user_id'] = $user->id;
                    $_SESSION['user_type'] = $user->type;
                    
                    $this->redirect('/dashboard');
                } catch (\Exception $e) {
                    $errors['register'] = [$e->getMessage()];
                }
            }
            
            return $this->view('auth/register', ['errors' => $errors]);
        }

        return $this->view('auth/register');
    }

    public function logout() {
        session_destroy();
        $this->redirect('/login');
    }

    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validate($_POST, [
                'email' => 'required|email'
            ]);

            if (empty($errors)) {
                try {
                    $this->authService->sendPasswordReset($_POST['email']);
                    return $this->view('auth/password-reset-sent');
                } catch (\Exception $e) {
                    $errors['reset'] = [$e->getMessage()];
                }
            }
            
            return $this->view('auth/reset-password', ['errors' => $errors]);
        }

        return $this->view('auth/reset-password');
    }
}

// app/Controllers/BaseController.php
<?php
namespace App\Controllers;

class BaseController {
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function view($template, $data = []) {
        extract($data);
        require __DIR__ . '/../../resources/views/' . $template . '.php';
    }

    protected function redirect($path) {
        header('Location: ' . $path);
        exit;
    }

    protected function validate($data, $rules) {
        $errors = [];
        foreach ($rules as $field => $rule) {
            if (strpos($rule, 'required') !== false && empty($data[$field])) {
                $errors[$field][] = ucfirst($field) . ' is required';
            }
            
            if (!empty($data[$field])) {
                if (strpos($rule, 'email') !== false && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = 'Invalid email format';
                }
                
                if (strpos($rule, 'min:') !== false) {
                    preg_match('/min:(\d+)/', $rule, $matches);
                    if (strlen($data[$field]) < $matches[1]) {
                        $errors[$field][] = ucfirst($field) . ' must be at least ' . $matches[1] . ' characters';
                    }
                }
            }
        }
        
        return $errors;
    }

    protected function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }

    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }
    }
}

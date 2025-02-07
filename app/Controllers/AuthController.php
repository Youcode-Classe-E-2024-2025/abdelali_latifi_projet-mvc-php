<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Core\Validator;
use App\Models\User;

class AuthController extends Controller {
    public function __construct() {
        parent::__construct();
    }
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? null)) {
                return $this->json(['error' => 'Invalid CSRF token'], 403);
            }
            $validator = new Validator($_POST);
            $validator->required('email')->email('email')
                     ->required('password')->minLength('password', 6);
            
            if ($validator->isValid()) {
                $user = User::findByEmail($_POST['email']);
                
                if ($user && $user->verifyPassword($_POST['password'])) {
                    $_SESSION['user_id'] = $user->getId();
                    $_SESSION['user_role'] = $user->getRole();
                    
                    $this->redirect('/dashboard');
                }
                
                $error = 'Invalid email or password';
            } else {
                $error = $validator->getErrors();
            }
        }
        
        return $this->render('auth/login', [
            'error' => $error ?? null,
            'email' => $_POST['email'] ?? ''
        ]);
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? null)) {
                return $this->json(['error' => 'Invalid CSRF token'], 403);
            }
            $validator = new Validator($_POST);
            $validator->required('email')->email('email')
                     ->required('password')->minLength('password', 6)
                     ->required('password_confirm')
                     ->matches('password_confirm', 'password');
            
            if ($validator->isValid()) {
                if (!User::findByEmail($_POST['email'])) {
                    $user = new User([
                        'email' => $_POST['email'],
                        'password' => $_POST['password'],
                        'role' => 'user'
                    ]);
                    
                    if ($user->save()) {
                        $_SESSION['user_id'] = $user->getId();
                        $_SESSION['user_role'] = $user->getRole();
                        
                        $this->redirect('/dashboard');
                    }
                    
                    $error = 'Error creating account';
                } else {
                    $error = 'Email already exists';
                }
            } else {
                $error = $validator->getErrors();
            }
        }
        
        return $this->render('auth/register', [
            'error' => $error ?? null,
            'email' => $_POST['email'] ?? ''
        ]);
    }
    
    public function logout() {
        // Clear all session data
        $_SESSION = [];
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login
        $this->redirect('/login');
    }
}

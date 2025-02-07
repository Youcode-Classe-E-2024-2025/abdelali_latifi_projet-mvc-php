<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Core\Validator;
use App\Models\User;
use App\Models\Article;

class DashboardController extends Controller {
    private function getParam(string $name): ?string {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        preg_match('/\/([^\/]+)\/([^\/]+)\/?$/', $path, $matches);
        return $matches[2] ?? null;
    }
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }
    
    public function index() {
        $user = User::findById($_SESSION['user_id']);
        $articles = Article::findByUserId($_SESSION['user_id']);
        
        return $this->render('dashboard/index', [
            'user' => $user,
            'articles' => $articles
        ]);
    }
    
    public function profile() {
        $user = User::findById($_SESSION['user_id']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? null)) {
                return $this->json(['error' => 'Invalid CSRF token'], 403);
            }
            
            $validator = new Validator($_POST);
            $validator->required('email')->email('email');
            
            if ($validator->isValid()) {
                $user->setEmail($_POST['email']);
                
                if (!empty($_POST['password'])) {
                    $validator->minLength('password', 6)
                             ->required('password_confirm')
                             ->matches('password_confirm', 'password');
                             
                    if ($validator->isValid()) {
                        $user->setPassword($_POST['password']);
                    }
                }
                
                if ($validator->isValid() && $user->save()) {
                    $success = 'Profile updated successfully';
                } else {
                    $error = 'Error updating profile';
                }
            } else {
                $error = $validator->getErrors();
            }
        }
        
        return $this->render('dashboard/profile', [
            'user' => $user,
            'error' => $error ?? null,
            'success' => $success ?? null
        ]);
    }

    public function createArticle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? null)) {
                return $this->json(['error' => 'Invalid CSRF token'], 403);
            }
            
            $validator = new Validator($_POST);
            $validator->required('title')->minLength('title', 3)
                     ->required('content')->minLength('content', 10);
            
            if ($validator->isValid()) {
                $article = new Article([
                    'title' => $_POST['title'],
                    'content' => $_POST['content'],
                    'user_id' => $_SESSION['user_id']
                ]);
                
                if ($article->save()) {
                    $this->redirect('/dashboard');
                }
                
                $error = 'Error creating article';
            } else {
                $error = $validator->getErrors();
            }
        }
        
        return $this->render('dashboard/create_article', [
            'error' => $error ?? null
        ]);
    }

    public function editArticle($id) {
        $article = Article::findById((int) $id);
        
        if (!$article || $article->getUserId() !== $_SESSION['user_id']) {
            $this->redirect('/dashboard');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? null)) {
                return $this->json(['error' => 'Invalid CSRF token'], 403);
            }
            
            $validator = new Validator($_POST);
            $validator->required('title')->minLength('title', 3)
                     ->required('content')->minLength('content', 10);
            
            if ($validator->isValid()) {
                $article->setTitle($_POST['title']);
                $article->setContent($_POST['content']);
                
                if ($article->save()) {
                    $this->redirect('/dashboard');
                }
                
                $error = 'Error updating article';
            } else {
                $error = $validator->getErrors();
            }
        }
        
        return $this->render('dashboard/edit_article', [
            'article' => $article,
            'error' => $error ?? null
        ]);
    }

    public function deleteArticle($id) {
        $article = Article::findById((int) $id);
        
        if ($article && $article->getUserId() === $_SESSION['user_id']) {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? null)) {
                $this->redirect('/dashboard');
                return;
            }
            
            if ($article->delete()) {
                $this->redirect('/dashboard');
                return;
            }
        }
        
        $this->redirect('/dashboard');
    }
}

<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Article;

class HomeController extends Controller {
    public function __construct() {
        parent::__construct();
    }
    public function index() {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }
        
        $articles = Article::findAll();
        return $this->render('home/index', ['articles' => $articles]);
    }
}

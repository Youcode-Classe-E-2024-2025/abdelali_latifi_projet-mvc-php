<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;

// Home route
$router->get('/', [HomeController::class, 'index']);

// Auth routes
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'register']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);

// Dashboard routes
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/profile', [DashboardController::class, 'profile']);
$router->post('/profile', [DashboardController::class, 'profile']);

// Article routes
$router->get('/dashboard/article/create', [DashboardController::class, 'createArticle']);
$router->post('/dashboard/article/create', [DashboardController::class, 'createArticle']);
$router->get('/dashboard/article/:id/edit', [DashboardController::class, 'editArticle']);
$router->post('/dashboard/article/:id/edit', [DashboardController::class, 'editArticle']);
$router->post('/dashboard/article/:id/delete', [DashboardController::class, 'deleteArticle']);
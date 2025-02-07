<?php

namespace App\Core;

abstract class Controller {
    protected $twig;
    
    public function __construct() {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../views');
        $this->twig = new \Twig\Environment($loader, [
            'cache' => false, // Disable caching
            'debug' => $_ENV['APP_ENV'] === 'development',
            'auto_reload' => true // Always reload templates
        ]);
        
        // Add the security extension
        $this->twig->addExtension(new \App\Core\Twig\SecurityExtension());
    }
    
    protected function render(string $view, array $data = []): string {
        // Add session data to all views
        $data['session'] = $_SESSION;
        $data['is_authenticated'] = $this->isAuthenticated();
        
        return $this->twig->render($view . '.twig', $data);
    }
    
    protected function json(array $data, int $status = 200): string {
        http_response_code($status);
        header('Content-Type: application/json');
        return json_encode($data);
    }
    
    protected function redirect(string $path): void {
        header("Location: {$path}");
        exit;
    }
    
    protected function isAuthenticated(): bool {
        return isset($_SESSION['user_id']);
    }
    
    protected function requireAuth(): void {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }
    }
}

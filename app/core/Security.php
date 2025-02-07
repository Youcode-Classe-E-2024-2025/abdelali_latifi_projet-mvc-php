<?php

namespace App\Core;

class Security {
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_ARGON2ID);
    }
    
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
    
    public static function sanitizeInput(string $input): string {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    
    public static function setupSecureSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            // Set session path to our logs/sessions directory
            $sessionPath = __DIR__ . '/../../logs/sessions';
            if (!is_dir($sessionPath)) {
                mkdir($sessionPath, 0775, true);
            }
            session_save_path($sessionPath);
            
            // Configure session security
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_secure', '0'); // Set to 0 for development
            ini_set('session.use_strict_mode', '1');
            ini_set('session.gc_maxlifetime', 3600); // 1 hour
            ini_set('session.cookie_lifetime', 0); // Session cookie
            ini_set('session.use_cookies', '1');
            ini_set('session.use_only_cookies', '1');
            
            session_start();
            
            // Initialize CSRF token if not set
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            
            // Regenerate session ID periodically
            if (!isset($_SESSION['last_regeneration'])) {
                self::regenerateSession();
            } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
                self::regenerateSession();
            }
        }
    }
    
    public static function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function validateCsrfToken(?string $token): bool {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public static function regenerateSession(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $old_session_data = $_SESSION;
            session_regenerate_id(true);
            $_SESSION = $old_session_data;
            $_SESSION['last_regeneration'] = time();
        }
    }
}

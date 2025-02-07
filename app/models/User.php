<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Security;

class User {
    private ?int $id = null;
    private string $email;
    private string $password;
    private string $role;
    private string $created_at;
    
    public function __construct(array $data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->email = $data['email'] ?? '';
            $this->password = $data['password'] ?? '';
            $this->role = $data['role'] ?? 'user';
            $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
        }
    }
    
    public function save(): bool {
        $db = Database::getInstance();
        
        if ($this->id === null) {
            $sql = "INSERT INTO users (email, password, role, created_at) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                $this->email,
                Security::hashPassword($this->password),
                $this->role,
                $this->created_at
            ]);
        } else {
            $sql = "UPDATE users SET email = ?, role = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            return $stmt->execute([$this->email, $this->role, $this->id]);
        }
    }
    
    public static function findByEmail(string $email): ?self {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($row = $stmt->fetch()) {
            return new self($row);
        }
        
        return null;
    }
    
    public static function findById(int $id): ?self {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($row = $stmt->fetch()) {
            return new self($row);
        }
        
        return null;
    }
    
    public function verifyPassword(string $password): bool {
        return Security::verifyPassword($password, $this->password);
    }
    
    // Getters
    public function getId(): ?int { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function getRole(): string { return $this->role; }
    public function getCreatedAt(): string { return $this->created_at; }
    
    // Setters
    public function setEmail(string $email): void { $this->email = $email; }
    public function setPassword(string $password): void { $this->password = $password; }
    public function setRole(string $role): void { $this->role = $role; }
}

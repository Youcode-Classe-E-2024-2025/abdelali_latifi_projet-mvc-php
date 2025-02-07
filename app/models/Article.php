<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Article {
    private ?int $id = null;
    private string $title;
    private string $content;
    private int $user_id;
    private string $created_at;
    private string $updated_at;
    private ?User $author = null;

    public function __construct(array $data = []) {
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'] ?? '';
        $this->content = $data['content'] ?? '';
        $this->user_id = $data['user_id'] ?? 0;
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updated_at = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }

    public function save(): bool {
        $db = Database::getInstance();
        
        if ($this->id === null) {
            // Insert new article
            $sql = "INSERT INTO articles (title, content, user_id) VALUES (:title, :content, :user_id)";
            $stmt = $db->prepare($sql);
            
            return $stmt->execute([
                'title' => $this->title,
                'content' => $this->content,
                'user_id' => $this->user_id
            ]);
        } else {
            // Update existing article
            $sql = "UPDATE articles SET title = :title, content = :content, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND user_id = :user_id";
            $stmt = $db->prepare($sql);
            
            return $stmt->execute([
                'id' => $this->id,
                'title' => $this->title,
                'content' => $this->content,
                'user_id' => $this->user_id
            ]);
        }
    }

    public function delete(): bool {
        if ($this->id === null) {
            return false;
        }

        $db = Database::getInstance();
        $sql = "DELETE FROM articles WHERE id = :id AND user_id = :user_id";
        $stmt = $db->prepare($sql);
        
        return $stmt->execute([
            'id' => $this->id,
            'user_id' => $this->user_id
        ]);
    }

    public static function findById(int $id): ?Article {
        $db = Database::getInstance();
        $sql = "SELECT * FROM articles WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? new Article($data) : null;
    }

    public static function findByUserId(int $userId): array {
        $db = Database::getInstance();
        $sql = "SELECT * FROM articles WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        return array_map(fn($data) => new Article($data), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function findAll(): array {
        $db = Database::getInstance();
        $sql = "SELECT a.*, u.email as author_email FROM articles a JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        return array_map(fn($data) => new Article($data), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function getUserId(): int {
        return $this->user_id;
    }

    public function getCreatedAt(): string {
        return $this->created_at;
    }

    public function getUpdatedAt(): string {
        return $this->updated_at;
    }

    public function getAuthor(): ?User {
        if ($this->author === null && $this->user_id) {
            $this->author = User::findById($this->user_id);
        }
        return $this->author;
    }

    // Setters
    public function setTitle(string $title): void {
        $this->title = $title;
    }

    public function setContent(string $content): void {
        $this->content = $content;
    }

    public function setUserId(int $userId): void {
        $this->user_id = $userId;
    }
}

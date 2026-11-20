<?php

class Post
{
    private PDO $pdo;
    public ?int $id = null;
    public ?int $user_id = null;
    public string $title = '';
    public string $content = '';
    public int $views = 0;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** @return Post[] */
    public static function all(PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT id, user_id, title, content, views FROM posts ORDER BY id');
        $posts = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $post = new Post($pdo);
            $post->id = (int) $row['id'];
            $post->user_id = isset($row['user_id']) ? (int) $row['user_id'] : null;
            $post->title = (string) $row['title'];
            $post->content = (string) $row['content'];
            $post->views = (int) ($row['views'] ?? 0);
            $posts[] = $post;
        }
        $stmt->closeCursor();
        return $posts;
    }

    public static function find(PDO $pdo, int $id): ?Post
    {
        $stmt = $pdo->prepare('SELECT id, user_id, title, content, views FROM posts WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($row === false) {
            return null;
        }

        $post = new Post($pdo);
        $post->id = (int) $row['id'];
        $post->user_id = isset($row['user_id']) ? (int) $row['user_id'] : null;
        $post->title = (string) $row['title'];
        $post->content = (string) $row['content'];
        $post->views = (int) ($row['views'] ?? 0);
        return $post;
    }

    public static function create(PDO $pdo, string $title, string $content, int $views = 0, ?int $userId = null): Post
    {
        $title = trim($title);
        $content = trim($content);

        $stmt = $pdo->prepare('INSERT INTO posts (user_id, title, content, views) VALUES (:user_id, :title, :content, :views)');
        $stmt->bindValue(':user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':content', $content, PDO::PARAM_STR);
        $stmt->bindValue(':views', $views, PDO::PARAM_INT);
        $stmt->execute();

        $id = (int) $pdo->lastInsertId();
        $stmt->closeCursor();

        $post = new Post($pdo);
        $post->id = $id;
        $post->user_id = $userId;
        $post->title = $title;
        $post->content = $content;
        $post->views = $views;
        return $post;
    }

    public function update(): bool
    {
        if ($this->id === null) {
            return false;
        }

        $this->title = trim($this->title);
        $this->content = trim($this->content);

        $stmt = $this->pdo->prepare('UPDATE posts SET title = :title, content = :content, views = :views WHERE id = :id');
        $stmt->bindValue(':title', $this->title, PDO::PARAM_STR);
        $stmt->bindValue(':content', $this->content, PDO::PARAM_STR);
        $stmt->bindValue(':views', $this->views, PDO::PARAM_INT);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->closeCursor();

        return true;
    }

    public static function delete(PDO $pdo, Post $post): bool
    {
        if ($post->id === null) {
            return false;
        }

        $stmt = $pdo->prepare('DELETE FROM posts WHERE id = :id');
        $stmt->bindValue(':id', $post->id, PDO::PARAM_INT);
        $stmt->execute();
        $deleted = $stmt->rowCount() === 1;
        $stmt->closeCursor();

        if ($deleted) {
            $post->id = null;
        }
        return $deleted;
    }
}
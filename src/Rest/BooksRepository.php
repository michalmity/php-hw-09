<?php declare(strict_types=1);

namespace Books\Rest;

use Books\Database\DB;
use PDO;

class BooksRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $databaseDir = __DIR__ . '/../../data';

        if (!is_dir($databaseDir)) 
        {
            mkdir($databaseDir, 0777, true);
        }

        $this->pdo = DB::getConnection();
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS books (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                author TEXT NOT NULL,
                publisher TEXT NOT NULL,
                isbn TEXT NOT NULL,
                pages INTEGER NOT NULL
            );
        ";
        $this->pdo->exec($sql);
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT id, name, author FROM books");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO books (name, author, publisher, isbn, pages) 
                VALUES (?,?,?,?,?)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$data['name'],$data['author'],$data['publisher'],$data['isbn'],$data['pages']]);

        return (int)$this->pdo->lastInsertId();
    }

    public function getById(int $id) : ?array
    {
        $sql = "SELECT * FROM books WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        $book = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($book === false)
        {
            return null;
        }
        
        return $book;
    }

    public function update(int $id, array $data): void
    {
        $sql = "UPDATE books SET name = ?, author = ?, publisher = ?, isbn = ?, pages = ? WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$data['name'],$data['author'],$data['publisher'],$data['isbn'],$data['pages'],$id]);
    }

    public function delete(int $id): void
    {
        $sql = "DELETE FROM books WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        
        $stmt->execute([$id]);
    }
}
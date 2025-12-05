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
        if (!is_dir($databaseDir)) {
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
                VALUES (:name, :author, :publisher, :isbn, :pages)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':author' => $data['author'],
            ':publisher' => $data['publisher'],
            ':isbn' => $data['isbn'],
            ':pages' => $data['pages'],
        ]);

        return (int)$this->pdo->lastInsertId();
    }
}
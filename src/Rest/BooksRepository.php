<?php declare(strict_types=1);

namespace Books\Rest;

use Books\Database\DB;
use PDO;

class BooksRepository
{
    private PDO $pdo;

    public function __construct()
    {

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
}
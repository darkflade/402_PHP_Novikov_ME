<?php
declare(strict_types=1);

function getDatabaseConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $databaseFile = __DIR__ . '/../db/game.sqlite';
    $databaseDir = dirname($databaseFile);

    if (!is_dir($databaseDir)) {
        mkdir($databaseDir, 0777, true);
    }

    $pdo = new PDO('sqlite:' . $databaseFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS games (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            player_name TEXT NOT NULL,
            played_at TEXT NOT NULL,
            number INTEGER NOT NULL,
            user_answer INTEGER NOT NULL,
            is_prime INTEGER NOT NULL,
            is_correct INTEGER NOT NULL,
            divisors TEXT NOT NULL
        )'
    );

    return $pdo;
}

function generateGameNumber(): int
{
    return random_int(2, 100);
}

function isPrimeNumber(int $number): bool
{
    if ($number < 2) {
        return false;
    }

    for ($i = 2; $i <= (int) sqrt((float) $number); $i++) {
        if ($number % $i === 0) {
            return false;
        }
    }

    return true;
}

function getNonTrivialDivisors(int $number): array
{
    $divisors = [];

    for ($i = 2; $i <= (int) floor($number / 2); $i++) {
        if ($number % $i === 0) {
            $divisors[] = $i;
        }
    }

    return $divisors;
}

function parseUserAnswer(?string $answer): ?bool
{
    if ($answer === 'yes') {
        return true;
    }

    if ($answer === 'no') {
        return false;
    }

    return null;
}

function saveGameResult(
    string $playerName,
    int $number,
    bool $userAnswer,
    bool $isPrime,
    bool $isCorrect,
    array $divisors
): void {
    $connection = getDatabaseConnection();
    $statement = $connection->prepare(
        'INSERT INTO games (player_name, played_at, number, user_answer, is_prime, is_correct, divisors)
         VALUES (:player_name, :played_at, :number, :user_answer, :is_prime, :is_correct, :divisors)'
    );

    $statement->execute(
        [
            ':player_name' => $playerName,
            ':played_at' => date('Y-m-d H:i:s'),
            ':number' => $number,
            ':user_answer' => $userAnswer ? 1 : 0,
            ':is_prime' => $isPrime ? 1 : 0,
            ':is_correct' => $isCorrect ? 1 : 0,
            ':divisors' => implode(', ', $divisors),
        ]
    );
}

function getGameHistory(): array
{
    $connection = getDatabaseConnection();
    $statement = $connection->query('SELECT * FROM games ORDER BY id DESC');

    if ($statement === false) {
        return [];
    }

    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

    return is_array($rows) ? $rows : [];
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

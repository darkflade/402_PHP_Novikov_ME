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

    initializeDatabase($pdo);

    return $pdo;
}

function initializeDatabase(?PDO $connection = null): void
{
    $pdo = $connection ?? getDatabaseConnectionWithoutInit();
    upgradeLegacySchema($pdo);

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS games (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            player_name TEXT NOT NULL,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS steps (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            game_id INTEGER NOT NULL,
            played_at TEXT NOT NULL,
            number INTEGER NOT NULL,
            user_answer TEXT NOT NULL,
            is_prime INTEGER NOT NULL,
            is_correct INTEGER NOT NULL,
            divisors TEXT NOT NULL,
            FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
        )'
    );
}

function upgradeLegacySchema(PDO $pdo): void
{
    $gameColumns = getTableColumns($pdo, 'games');

    if ($gameColumns !== [] && (!in_array('created_at', $gameColumns, true) || !in_array('updated_at', $gameColumns, true))) {
        $pdo->exec('DROP TABLE IF EXISTS steps');
        $pdo->exec('DROP TABLE IF EXISTS games');
    }
}

function getTableColumns(PDO $pdo, string $tableName): array
{
    $statement = $pdo->query('PRAGMA table_info(' . $tableName . ')');

    if ($statement === false) {
        return [];
    }

    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
    if (!is_array($rows)) {
        return [];
    }

    $columns = [];
    foreach ($rows as $row) {
        $columns[] = (string) ($row['name'] ?? '');
    }

    return $columns;
}

function getDatabaseConnectionWithoutInit(): PDO
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

function parseUserAnswer(?string $answer): ?string
{
    if ($answer === null) {
        return null;
    }

    $normalizedAnswer = strtolower(trim($answer));

    if ($normalizedAnswer === 'yes') {
        return 'yes';
    }

    if ($normalizedAnswer === 'no') {
        return 'no';
    }

    return null;
}

function createGame(string $playerName): int
{
    $connection = getDatabaseConnection();
    $statement = $connection->prepare(
        'INSERT INTO games (player_name, created_at, updated_at)
         VALUES (:player_name, :created_at, :updated_at)'
    );

    $timestamp = date('Y-m-d H:i:s');
    $statement->execute(
        [
            ':player_name' => $playerName,
            ':created_at' => $timestamp,
            ':updated_at' => $timestamp,
        ]
    );

    return (int) $connection->lastInsertId();
}

function addStepToGame(int $gameId, int $number, string $userAnswer): array
{
    $isPrime = isPrimeNumber($number);
    $isCorrect = ($userAnswer === 'yes' && $isPrime) || ($userAnswer === 'no' && !$isPrime);
    $divisors = $isPrime ? [] : getNonTrivialDivisors($number);

    $connection = getDatabaseConnection();
    $statement = $connection->prepare(
        'INSERT INTO steps (game_id, played_at, number, user_answer, is_prime, is_correct, divisors)
         VALUES (:game_id, :played_at, :number, :user_answer, :is_prime, :is_correct, :divisors)'
    );

    $playedAt = date('Y-m-d H:i:s');
    $statement->execute(
        [
            ':game_id' => $gameId,
            ':played_at' => $playedAt,
            ':number' => $number,
            ':user_answer' => $userAnswer,
            ':is_prime' => $isPrime ? 1 : 0,
            ':is_correct' => $isCorrect ? 1 : 0,
            ':divisors' => implode(', ', $divisors),
        ]
    );

    $updateStatement = $connection->prepare(
        'UPDATE games SET updated_at = :updated_at WHERE id = :id'
    );
    $updateStatement->execute(
        [
            ':updated_at' => $playedAt,
            ':id' => $gameId,
        ]
    );

    return [
        'id' => (int) $connection->lastInsertId(),
        'game_id' => $gameId,
        'played_at' => $playedAt,
        'number' => $number,
        'user_answer' => $userAnswer,
        'is_prime' => $isPrime,
        'is_correct' => $isCorrect,
        'divisors' => $divisors,
    ];
}

function getGames(): array
{
    $connection = getDatabaseConnection();
    $statement = $connection->query(
        'SELECT
            g.id,
            g.player_name,
            g.created_at,
            g.updated_at,
            COUNT(s.id) AS steps_count
         FROM games g
         LEFT JOIN steps s ON s.game_id = g.id
         GROUP BY g.id
         ORDER BY g.id DESC'
    );

    if ($statement === false) {
        return [];
    }

    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

    return is_array($rows) ? $rows : [];
}

function getGameById(int $gameId): ?array
{
    $connection = getDatabaseConnection();

    $gameStatement = $connection->prepare(
        'SELECT id, player_name, created_at, updated_at
         FROM games
         WHERE id = :id'
    );
    $gameStatement->execute([':id' => $gameId]);
    $game = $gameStatement->fetch(PDO::FETCH_ASSOC);

    if (!is_array($game)) {
        return null;
    }

    $stepsStatement = $connection->prepare(
        'SELECT id, game_id, played_at, number, user_answer, is_prime, is_correct, divisors
         FROM steps
         WHERE game_id = :game_id
         ORDER BY id ASC'
    );
    $stepsStatement->execute([':game_id' => $gameId]);
    $steps = $stepsStatement->fetchAll(PDO::FETCH_ASSOC);

    $normalizedSteps = [];
    if (is_array($steps)) {
        foreach ($steps as $step) {
            $normalizedSteps[] = normalizeStepRow($step);
        }
    }

    return [
        'id' => (int) $game['id'],
        'player_name' => (string) $game['player_name'],
        'created_at' => (string) $game['created_at'],
        'updated_at' => (string) $game['updated_at'],
        'steps' => $normalizedSteps,
    ];
}

function normalizeGames(array $rows): array
{
    $result = [];

    foreach ($rows as $row) {
        $result[] = [
            'id' => (int) $row['id'],
            'player_name' => (string) $row['player_name'],
            'created_at' => (string) $row['created_at'],
            'updated_at' => (string) $row['updated_at'],
            'steps_count' => (int) $row['steps_count'],
        ];
    }

    return $result;
}

function normalizeStepRow(array $step): array
{
    $divisorsRaw = trim((string) ($step['divisors'] ?? ''));
    $divisors = [];

    if ($divisorsRaw !== '') {
        $divisors = array_map(
            static fn(string $value): int => (int) trim($value),
            explode(',', $divisorsRaw)
        );
    }

    return [
        'id' => (int) $step['id'],
        'game_id' => (int) $step['game_id'],
        'played_at' => (string) $step['played_at'],
        'number' => (int) $step['number'],
        'user_answer' => (string) $step['user_answer'],
        'is_prime' => ((int) $step['is_prime']) === 1,
        'is_correct' => ((int) $step['is_correct']) === 1,
        'divisors' => $divisors,
    ];
}

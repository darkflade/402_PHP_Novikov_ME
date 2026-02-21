<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/game.php';

$history = getGameHistory();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prime Game History</title>
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
<main class="container">
    <h1>Game History</h1>
    <p><a class="link" href="/index.php">Back to home</a> | <a class="link" href="/play.php">Play again</a></p>

    <?php if ($history === []): ?>
        <section class="panel">
            <p>No games played yet.</p>
        </section>
    <?php else: ?>
        <section class="panel">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Player</th>
                    <th>Date</th>
                    <th>Number</th>
                    <th>Answer</th>
                    <th>Prime</th>
                    <th>Result</th>
                    <th>Divisors</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($history as $row): ?>
                    <tr>
                        <td><?= (int) $row['id'] ?></td>
                        <td><?= e((string) $row['player_name']) ?></td>
                        <td><?= e((string) $row['played_at']) ?></td>
                        <td><?= (int) $row['number'] ?></td>
                        <td><?= ((int) $row['user_answer']) === 1 ? 'yes' : 'no' ?></td>
                        <td><?= ((int) $row['is_prime']) === 1 ? 'yes' : 'no' ?></td>
                        <td><?= ((int) $row['is_correct']) === 1 ? 'win' : 'loss' ?></td>
                        <td><?= e((string) $row['divisors']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>
</main>
</body>
</html>

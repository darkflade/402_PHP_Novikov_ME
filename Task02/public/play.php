<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/game.php';

$playerName = '';
$selectedAnswer = '';
$number = generateGameNumber();
$errors = [];
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST'):
    $playerName = trim((string) ($_POST['player_name'] ?? ''));
    $selectedAnswer = (string) ($_POST['answer'] ?? '');
    $inputNumber = filter_input(INPUT_POST, 'number', FILTER_VALIDATE_INT);

    if (is_int($inputNumber) && $inputNumber >= 2):
        $number = $inputNumber;
    endif;

    if ($playerName === ''):
        $errors[] = 'Enter player name.';
    endif;

    $userSaysPrime = parseUserAnswer($selectedAnswer);
    if ($userSaysPrime === null):
        $errors[] = 'Select an answer.';
    endif;

    if ($errors === [] && $userSaysPrime !== null):
        $isPrime = isPrimeNumber($number);
        $isCorrect = ($userSaysPrime === $isPrime);
        $divisors = $isPrime ? [] : getNonTrivialDivisors($number);

        saveGameResult($playerName, $number, $userSaysPrime, $isPrime, $isCorrect, $divisors);

        $result = [
            'number' => $number,
            'is_prime' => $isPrime,
            'is_correct' => $isCorrect,
            'divisors' => $divisors,
        ];

        $number = generateGameNumber();
        $selectedAnswer = '';
    endif;
endif;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Play Prime Game</title>
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
<main class="container">
    <h1>Prime Game</h1>
    <p><a class="link" href="/index.php">Back to home</a> | <a class="link" href="/history.php">View history</a></p>

    <?php if ($errors !== []): ?>
        <section class="panel panel-error">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <?php if ($result !== null): ?>
        <section class="panel">
            <h2>Last round</h2>
            <p>Number: <strong><?= $result['number'] ?></strong></p>
            <p>
                Correct answer:
                <strong><?= $result['is_prime'] ? 'yes' : 'no' ?></strong>
            </p>
            <p>
                Result:
                <strong><?= $result['is_correct'] ? 'Correct' : 'Wrong' ?></strong>
            </p>
            <?php if ($result['is_prime'] === false): ?>
                <p>Non-trivial divisors: <strong><?= e(implode(', ', $result['divisors'])) ?></strong></p>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <section class="panel">
        <h2>New round</h2>
        <form method="post" action="/play.php">
            <label for="player_name">Player name</label>
            <input id="player_name" type="text" name="player_name" value="<?= e($playerName) ?>" required>

            <p>Is number <strong><?= $number ?></strong> prime?</p>
            <input type="hidden" name="number" value="<?= $number ?>">

            <label class="choice">
                <input type="radio" name="answer" value="yes" <?= $selectedAnswer === 'yes' ? 'checked' : '' ?>>
                Yes
            </label>
            <label class="choice">
                <input type="radio" name="answer" value="no" <?= $selectedAnswer === 'no' ? 'checked' : '' ?>>
                No
            </label>

            <button class="button" type="submit">Submit</button>
        </form>
    </section>
</main>
</body>
</html>

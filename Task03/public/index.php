<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/game.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

initializeDatabase();

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response): Response {
    $indexFile = __DIR__ . '/index.html';
    $html = file_get_contents($indexFile);

    if ($html === false) {
        return jsonResponse($response, ['error' => 'Unable to read index.html'], 500);
    }

    $response->getBody()->write($html);

    return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
});

$app->get('/games', function (Request $request, Response $response): Response {
    $games = normalizeGames(getGames());

    return jsonResponse($response, ['games' => $games]);
});

$app->get('/games/{id:[0-9]+}', function (Request $request, Response $response, array $args): Response {
    $gameId = (int) ($args['id'] ?? 0);
    $game = getGameById($gameId);

    if ($game === null) {
        return jsonResponse($response, ['error' => 'Game not found'], 404);
    }

    return jsonResponse($response, $game);
});

$app->post('/games', function (Request $request, Response $response): Response {
    $data = (array) $request->getParsedBody();
    $playerName = trim((string) ($data['player_name'] ?? ''));

    if ($playerName === '') {
        return jsonResponse($response, ['error' => 'player_name is required'], 400);
    }

    $gameId = createGame($playerName);

    return jsonResponse($response, ['id' => $gameId], 201);
});

$app->post('/step/{id:[0-9]+}', function (Request $request, Response $response, array $args): Response {
    $gameId = (int) ($args['id'] ?? 0);
    $game = getGameById($gameId);

    if ($game === null) {
        return jsonResponse($response, ['error' => 'Game not found'], 404);
    }

    $data = (array) $request->getParsedBody();
    $number = filter_var($data['number'] ?? null, FILTER_VALIDATE_INT);
    $userAnswer = parseUserAnswer(isset($data['user_answer']) ? (string) $data['user_answer'] : null);

    if (!is_int($number) || $number < 2) {
        return jsonResponse($response, ['error' => 'number must be an integer >= 2'], 400);
    }

    if ($userAnswer === null) {
        return jsonResponse($response, ['error' => "user_answer must be 'yes' or 'no'"], 400);
    }

    $step = addStepToGame($gameId, $number, $userAnswer);

    return jsonResponse($response, $step, 201);
});

$app->run();

function jsonResponse(Response $response, array $payload, int $statusCode = 200): Response
{
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        $response->getBody()->write('{"error":"json encode failed"}');

        return $response
            ->withStatus(500)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
    }

    $response->getBody()->write($json);

    return $response
        ->withStatus($statusCode)
        ->withHeader('Content-Type', 'application/json; charset=utf-8');
}

<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Step;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GameController extends Controller
{
    public function index(): JsonResponse
    {
        $games = Game::query()
            ->withCount('steps')
            ->orderByDesc('id')
            ->get()
            ->map(function (Game $game): array {
                return [
                    'id' => $game->id,
                    'player_name' => $game->player_name,
                    'created_at' => $game->created_at?->format('Y-m-d H:i:s'),
                    'updated_at' => $game->updated_at?->format('Y-m-d H:i:s'),
                    'steps_count' => $game->steps_count,
                ];
            })
            ->values();

        return response()->json(['games' => $games]);
    }

    public function show(int $id): JsonResponse
    {
        $game = Game::query()
            ->with(['steps' => fn ($query) => $query->orderBy('id')])
            ->find($id);

        if ($game === null) {
            return response()->json(['error' => 'Game not found'], 404);
        }

        return response()->json([
            'id' => $game->id,
            'player_name' => $game->player_name,
            'created_at' => $game->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $game->updated_at?->format('Y-m-d H:i:s'),
            'steps' => $game->steps
                ->map(fn (Step $step): array => $this->normalizeStep($step))
                ->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $playerName = trim((string) $request->input('player_name', ''));

        if ($playerName === '') {
            return response()->json(['error' => 'player_name is required'], 400);
        }

        $game = Game::query()->create([
            'player_name' => $playerName,
        ]);

        return response()->json(['id' => $game->id], 201);
    }

    public function step(Request $request, int $id): JsonResponse
    {
        $game = Game::query()->find($id);
        if ($game === null) {
            return response()->json(['error' => 'Game not found'], 404);
        }

        $number = filter_var($request->input('number'), FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        $userAnswer = $this->parseUserAnswer($request->input('user_answer'));

        if (!is_int($number) || $number < 2) {
            return response()->json(['error' => 'number must be an integer >= 2'], 400);
        }

        if ($userAnswer === null) {
            return response()->json(['error' => "user_answer must be 'yes' or 'no'"], 400);
        }

        $isPrime = $this->isPrimeNumber($number);
        $isCorrect = ($userAnswer === 'yes' && $isPrime) || ($userAnswer === 'no' && !$isPrime);
        $divisors = $isPrime ? [] : $this->getNonTrivialDivisors($number);
        $playedAt = Carbon::now();

        $step = Step::query()->create([
            'game_id' => $game->id,
            'played_at' => $playedAt,
            'number' => $number,
            'user_answer' => $userAnswer,
            'is_prime' => $isPrime,
            'is_correct' => $isCorrect,
            'divisors' => implode(', ', $divisors),
        ]);

        $game->updated_at = $playedAt;
        $game->save();

        return response()->json($this->normalizeStep($step), 201);
    }

    private function normalizeStep(Step $step): array
    {
        $divisorsRaw = trim($step->divisors ?? '');
        $divisors = [];

        if ($divisorsRaw !== '') {
            $divisors = array_map(
                static fn (string $value): int => (int) trim($value),
                explode(',', $divisorsRaw)
            );
        }

        return [
            'id' => $step->id,
            'game_id' => $step->game_id,
            'played_at' => $step->played_at?->format('Y-m-d H:i:s'),
            'number' => $step->number,
            'user_answer' => $step->user_answer,
            'is_prime' => $step->is_prime,
            'is_correct' => $step->is_correct,
            'divisors' => $divisors,
        ];
    }

    private function parseUserAnswer(mixed $answer): ?string
    {
        if ($answer === null) {
            return null;
        }

        $normalizedAnswer = strtolower(trim((string) $answer));

        if ($normalizedAnswer === 'yes') {
            return 'yes';
        }

        if ($normalizedAnswer === 'no') {
            return 'no';
        }

        return null;
    }

    private function isPrimeNumber(int $number): bool
    {
        if ($number < 2) {
            return false;
        }

        $limit = (int) floor(sqrt((float) $number));

        for ($i = 2; $i <= $limit; $i++) {
            if ($number % $i === 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<int>
     */
    private function getNonTrivialDivisors(int $number): array
    {
        $divisors = [];

        for ($i = 2; $i <= (int) floor($number / 2); $i++) {
            if ($number % $i === 0) {
                $divisors[] = $i;
            }
        }

        return $divisors;
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_game_lifecycle_api(): void
    {
        $createResponse = $this->postJson('/games', ['player_name' => 'Alex']);
        $createResponse
            ->assertCreated()
            ->assertJsonStructure(['id']);

        $gameId = (int) $createResponse->json('id');

        $this->postJson("/step/{$gameId}", [
            'number' => 9,
            'user_answer' => 'no',
        ])
            ->assertCreated()
            ->assertJson([
                'game_id' => $gameId,
                'number' => 9,
                'user_answer' => 'no',
                'is_prime' => false,
                'is_correct' => true,
                'divisors' => [3],
            ]);

        $this->getJson("/games/{$gameId}")
            ->assertOk()
            ->assertJsonPath('id', $gameId)
            ->assertJsonPath('steps.0.number', 9)
            ->assertJsonPath('steps.0.is_correct', true);

        $this->getJson('/games')
            ->assertOk()
            ->assertJsonPath('games.0.id', $gameId)
            ->assertJsonPath('games.0.steps_count', 1);
    }

    public function test_validation_and_not_found_errors(): void
    {
        $this->postJson('/games', [])
            ->assertStatus(400)
            ->assertJson(['error' => 'player_name is required']);

        $this->postJson('/step/999', [
            'number' => 2,
            'user_answer' => 'yes',
        ])
            ->assertStatus(404)
            ->assertJson(['error' => 'Game not found']);

        $createResponse = $this->postJson('/games', ['player_name' => 'Max']);
        $gameId = (int) $createResponse->json('id');

        $this->postJson("/step/{$gameId}", [
            'number' => 1,
            'user_answer' => 'yes',
        ])
            ->assertStatus(400)
            ->assertJson(['error' => 'number must be an integer >= 2']);

        $this->postJson("/step/{$gameId}", [
            'number' => 7,
            'user_answer' => 'maybe',
        ])
            ->assertStatus(400)
            ->assertJson(['error' => "user_answer must be 'yes' or 'no'"]);
    }
}

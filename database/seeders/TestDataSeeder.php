<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\GameScore;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users
        $users = [
            [
                'username' => 'testuser1',
                'email' => 'test1@example.com',
                'password' => Hash::make('password'),
                'first_name' => 'John',
                'last_name' => 'Doe',
                'total_games' => 15,
                'best_score' => 2500,
                'total_score' => 18750,
            ],
            [
                'username' => 'testuser2',
                'email' => 'test2@example.com',
                'password' => Hash::make('password'),
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'total_games' => 12,
                'best_score' => 3200,
                'total_score' => 22400,
            ],
            [
                'username' => 'testuser3',
                'email' => 'test3@example.com',
                'password' => Hash::make('password'),
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'total_games' => 8,
                'best_score' => 1800,
                'total_score' => 9600,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create($userData);
            
            // Create some scores for each user
            $difficulties = ['easy', 'normal', 'hard'];
            $scores = [
                ['score' => $userData['best_score'], 'level' => 15, 'duration' => 450],
                ['score' => round($userData['best_score'] * 0.8), 'level' => 12, 'duration' => 380],
                ['score' => round($userData['best_score'] * 0.6), 'level' => 9, 'duration' => 280],
                ['score' => round($userData['best_score'] * 0.4), 'level' => 6, 'duration' => 180],
                ['score' => round($userData['best_score'] * 0.2), 'level' => 3, 'duration' => 90],
            ];

            foreach ($scores as $index => $scoreData) {
                GameScore::create([
                    'user_id' => $user->id,
                    'score' => $scoreData['score'],
                    'level' => $scoreData['level'],
                    'game_duration' => $scoreData['duration'],
                    'difficulty' => $difficulties[$index % 3],
                    'game_stats' => [
                        'food_eaten' => round($scoreData['score'] / 10),
                        'power_ups_used' => rand(2, 8),
                        'walls_hit' => rand(0, 5),
                    ],
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }
}

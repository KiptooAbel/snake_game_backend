<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameScore;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class LeaderboardController extends Controller
{
    /**
     * Get global leaderboard (all-time)
     */
    public function global(): JsonResponse
    {
        $leaderboard = User::select('id', 'username', 'best_score')
            ->where('best_score', '>', 0)
            ->orderBy('best_score', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'username' => $user->username,
                    'score' => $user->best_score,
                ];
            });

        return response()->json($leaderboard);
    }

    /**
     * Get daily leaderboard
     */
    public function daily(): JsonResponse
    {
        $today = Carbon::today();
        
        $leaderboard = GameScore::with('user:id,username')
            ->whereDate('created_at', $today)
            ->selectRaw('user_id, username, MAX(score) as score')
            ->join('users', 'scores.user_id', '=', 'users.id')
            ->groupBy('user_id', 'username')
            ->orderBy('score', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($score, $index) {
                return [
                    'rank' => $index + 1,
                    'username' => $score->username,
                    'score' => $score->score,
                ];
            });

        return response()->json($leaderboard);
    }

    /**
     * Get weekly leaderboard
     */
    public function weekly(): JsonResponse
    {
        $weekStart = Carbon::now()->startOfWeek();
        
        $leaderboard = GameScore::with('user:id,username')
            ->where('created_at', '>=', $weekStart)
            ->selectRaw('user_id, username, MAX(score) as score')
            ->join('users', 'scores.user_id', '=', 'users.id')
            ->groupBy('user_id', 'username')
            ->orderBy('score', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($score, $index) {
                return [
                    'rank' => $index + 1,
                    'username' => $score->username,
                    'score' => $score->score,
                ];
            });

        return response()->json($leaderboard);
    }

    /**
     * Get monthly leaderboard
     */
    public function monthly(): JsonResponse
    {
        $monthStart = Carbon::now()->startOfMonth();
        
        $leaderboard = GameScore::with('user:id,username')
            ->where('created_at', '>=', $monthStart)
            ->selectRaw('user_id, username, MAX(score) as score')
            ->join('users', 'scores.user_id', '=', 'users.id')
            ->groupBy('user_id', 'username')
            ->orderBy('score', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($score, $index) {
                return [
                    'rank' => $index + 1,
                    'username' => $score->username,
                    'score' => $score->score,
                ];
            });

        return response()->json($leaderboard);
    }

    /**
     * Get leaderboard by difficulty
     */
    public function byDifficulty(string $difficulty): JsonResponse
    {
        $validDifficulties = ['easy', 'normal', 'hard'];
        
        if (!in_array(strtolower($difficulty), $validDifficulties)) {
            return response()->json([
                'error' => 'Invalid difficulty level'
            ], 400);
        }

        $leaderboard = GameScore::with('user:id,username')
            ->where('difficulty', strtolower($difficulty))
            ->selectRaw('user_id, username, MAX(score) as score')
            ->join('users', 'scores.user_id', '=', 'users.id')
            ->groupBy('user_id', 'username')
            ->orderBy('score', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($score, $index) {
                return [
                    'rank' => $index + 1,
                    'username' => $score->username,
                    'score' => $score->score,
                ];
            });

        return response()->json($leaderboard);
    }
}
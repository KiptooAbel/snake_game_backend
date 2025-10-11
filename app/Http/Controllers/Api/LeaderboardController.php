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
        
        // Get user IDs with their max scores for today
        $topScores = GameScore::whereDate('created_at', $today)
            ->selectRaw('user_id, MAX(score) as max_score')
            ->groupBy('user_id')
            ->orderBy('max_score', 'desc')
            ->limit(100)
            ->get();

        // Build leaderboard with user details
        $leaderboard = $topScores->map(function ($scoreRecord, $index) {
            $user = \App\Models\User::select('id', 'username')->find($scoreRecord->user_id);
            return [
                'rank' => $index + 1,
                'username' => $user ? $user->username : 'Unknown',
                'score' => $scoreRecord->max_score,
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
        
        // Get user IDs with their max scores for this week
        $topScores = GameScore::where('created_at', '>=', $weekStart)
            ->selectRaw('user_id, MAX(score) as max_score')
            ->groupBy('user_id')
            ->orderBy('max_score', 'desc')
            ->limit(100)
            ->get();

        // Build leaderboard with user details
        $leaderboard = $topScores->map(function ($scoreRecord, $index) {
            $user = \App\Models\User::select('id', 'username')->find($scoreRecord->user_id);
            return [
                'rank' => $index + 1,
                'username' => $user ? $user->username : 'Unknown',
                'score' => $scoreRecord->max_score,
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
        
        // Get user IDs with their max scores for this month
        $topScores = GameScore::where('created_at', '>=', $monthStart)
            ->selectRaw('user_id, MAX(score) as max_score')
            ->groupBy('user_id')
            ->orderBy('max_score', 'desc')
            ->limit(100)
            ->get();

        // Build leaderboard with user details
        $leaderboard = $topScores->map(function ($scoreRecord, $index) {
            $user = \App\Models\User::select('id', 'username')->find($scoreRecord->user_id);
            return [
                'rank' => $index + 1,
                'username' => $user ? $user->username : 'Unknown',
                'score' => $scoreRecord->max_score,
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

        // Get user IDs with their max scores for this difficulty
        $topScores = GameScore::where('difficulty', strtolower($difficulty))
            ->selectRaw('user_id, MAX(score) as max_score')
            ->groupBy('user_id')
            ->orderBy('max_score', 'desc')
            ->limit(100)
            ->get();

        // Build leaderboard with user details
        $leaderboard = $topScores->map(function ($scoreRecord, $index) {
            $user = \App\Models\User::select('id', 'username')->find($scoreRecord->user_id);
            return [
                'rank' => $index + 1,
                'username' => $user ? $user->username : 'Unknown',
                'score' => $scoreRecord->max_score,
            ];
        });

        return response()->json($leaderboard);
    }
}
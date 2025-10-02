<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameScore;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class GameScoreController extends Controller
{
    /**
     * Get user's score history
     */
    public function index(): JsonResponse
    {
        $user = auth('api')->user();
        
        $scores = $user->gameScores()
            ->orderBy('score', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($scores);
    }

    /**
     * Store a new game score
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'score' => 'required|integer|min:0',
            'level' => 'required|integer|min:1',
            'game_duration' => 'required|integer|min:0',
            'difficulty' => 'required|string|in:easy,normal,hard',
            'game_stats' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth('api')->user();
        
        $score = GameScore::create([
            'user_id' => $user->id,
            'score' => $request->score,
            'level' => $request->level,
            'game_duration' => $request->game_duration,
            'difficulty' => $request->difficulty,
            'game_stats' => $request->game_stats,
        ]);

        // Update user statistics
        $user->increment('total_games');
        $user->increment('total_score', $request->score);
        
        if ($request->score > $user->best_score) {
            $user->update(['best_score' => $request->score]);
        }

        return response()->json($score->load('user'), 201);
    }

    /**
     * Get a specific score
     */
    public function show(GameScore $gameScore): JsonResponse
    {
        // Check if the score belongs to the authenticated user
        if ($gameScore->user_id !== auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $gameScore->load('user')
        ]);
    }

    /**
     * Delete a specific score
     */
    public function destroy(GameScore $gameScore): JsonResponse
    {
        // Check if the score belongs to the authenticated user
        if ($gameScore->user_id !== auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $gameScore->delete();

        return response()->json([
            'success' => true,
            'message' => 'Score deleted successfully'
        ]);
    }

    /**
     * Get user's best scores
     */
    public function best(): JsonResponse
    {
        $user = auth('api')->user();
        
        $bestScores = $user->gameScores()
            ->selectRaw('difficulty, MAX(score) as best_score, level, game_duration, created_at')
            ->groupBy('difficulty')
            ->orderBy('best_score', 'desc')
            ->get();

        return response()->json($bestScores);
    }

    /**
     * Get high scores (public endpoint)
     */
    public function highScores(Request $request): JsonResponse
    {
        $difficulty = $request->get('difficulty');
        $limit = $request->integer('limit', 10);

        $query = GameScore::with('user:id,username');

        if ($difficulty) {
            $query->where('difficulty', $difficulty);
        }

        $highScores = $query->orderBy('score', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($score, $index) {
                return [
                    'rank' => $index + 1,
                    'username' => $score->user->username,
                    'score' => $score->score,
                    'level' => $score->level,
                    'difficulty' => $score->difficulty,
                    'created_at' => $score->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $highScores
        ]);
    }
}

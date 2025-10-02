<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Get user profile
     */
    public function profile()
    {
        $user = auth('api')->user();
        
        return response()->json($user);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:255|unique:users,username,' . $user->id,
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only(['first_name', 'last_name', 'username', 'email']));

        return response()->json($user->fresh());
    }

    /**
     * Get user statistics
     */
    public function stats()
    {
        $user = auth('api')->user();
        
        $recentScores = $user->gameScores()
            ->latest()
            ->take(10)
            ->get();
            
        $averageScore = $user->gameScores()
            ->avg('score');
            
        $totalGamesThisWeek = $user->gameScores()
            ->where('created_at', '>=', now()->subWeek())
            ->count();
            
        $bestScoreThisMonth = $user->gameScores()
            ->where('created_at', '>=', now()->subMonth())
            ->max('score');

        // Calculate user's current rank
        $rank = \DB::table('users')
            ->where('best_score', '>', $user->best_score)
            ->count() + 1;

        return response()->json([
            'total_games' => $user->total_games,
            'best_score' => $user->best_score,
            'total_score' => $user->total_score,
            'average_score' => round($averageScore, 2),
            'rank' => $rank
        ]);
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth('api')->user();

        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
        
        $user->update(['avatar' => $avatarPath]);

        return response()->json([
            'success' => true,
            'message' => 'Avatar uploaded successfully',
            'avatar_url' => Storage::disk('public')->url($avatarPath)
        ]);
    }
}

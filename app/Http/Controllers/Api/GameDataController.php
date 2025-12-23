<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GameDataController extends Controller
{
    /**
     * Get user's game data
     */
    public function getData()
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'gems' => $user->gems ?? 0,
            'hearts' => $user->hearts ?? 0,
            'unlocked_levels' => $user->unlocked_levels ?? [1],
            'high_score' => $user->best_score ?? 0,
        ]);
    }

    /**
     * Sync game data between device and server
     * Merges local and server data, taking the maximum values
     */
    public function sync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gems' => 'required|integer|min:0',
            'hearts' => 'required|integer|min:0',
            'unlocked_levels' => 'required|array',
            'unlocked_levels.*' => 'integer|min:1',
            'high_score' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        
        // Get local data from request
        $localGems = $request->gems;
        $localHearts = $request->hearts;
        $localUnlockedLevels = $request->unlocked_levels;
        $localHighScore = $request->high_score;
        
        // Get server data
        $serverGems = $user->gems ?? 0;
        $serverHearts = $user->hearts ?? 0;
        $serverUnlockedLevels = $user->unlocked_levels ?? [1];
        $serverHighScore = $user->best_score ?? 0;
        
        // Merge data: take maximum values for gems, hearts, and high score
        $mergedGems = max($localGems, $serverGems);
        $mergedHearts = max($localHearts, $serverHearts);
        $mergedHighScore = max($localHighScore, $serverHighScore);
        
        // Merge unlocked levels: union of both arrays
        $mergedUnlockedLevels = array_unique(array_merge($localUnlockedLevels, $serverUnlockedLevels));
        sort($mergedUnlockedLevels);
        
        // Update user's game data
        $user->gems = $mergedGems;
        $user->hearts = $mergedHearts;
        $user->unlocked_levels = $mergedUnlockedLevels;
        $user->best_score = $mergedHighScore;
        $user->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Game data synced successfully',
            'gems' => $mergedGems,
            'hearts' => $mergedHearts,
            'unlocked_levels' => $mergedUnlockedLevels,
            'high_score' => $mergedHighScore,
        ]);
    }

    /**
     * Update specific game data field
     */
    public function updateField(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'field' => 'required|in:gems,hearts,unlocked_levels,high_score',
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $field = $request->field;
        $value = $request->value;
        
        // Map high_score to best_score in database
        if ($field === 'high_score') {
            $field = 'best_score';
        }
        
        // Validate value type based on field
        if (in_array($field, ['gems', 'hearts', 'best_score'])) {
            if (!is_numeric($value) || $value < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Value must be a non-negative number',
                ], 422);
            }
            $value = (int) $value;
        } elseif ($field === 'unlocked_levels') {
            if (!is_array($value)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unlocked levels must be an array',
                ], 422);
            }
        }
        
        $user->$field = $value;
        $user->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Game data updated successfully',
            $field => $value,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class UserGameDataController extends Controller
{
    /**
     * Get user's game data (gems, hearts, unlocked levels)
     */
    public function getGameData(): JsonResponse
    {
        $user = auth('api')->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'gems' => $user->gems ?? 0,
                'hearts' => $user->hearts ?? 5,
                'unlocked_levels' => $user->unlocked_levels ?? [],
            ]
        ]);
    }

    /**
     * Update user's game data (gems, hearts, unlocked levels)
     */
    public function updateGameData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'gems' => 'nullable|integer|min:0',
            'hearts' => 'nullable|integer|min:0|max:5',
            'unlocked_levels' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth('api')->user();
        
        $updateData = [];
        if ($request->has('gems')) {
            $updateData['gems'] = $request->gems;
        }
        if ($request->has('hearts')) {
            $updateData['hearts'] = $request->hearts;
        }
        if ($request->has('unlocked_levels')) {
            $updateData['unlocked_levels'] = $request->unlocked_levels;
        }

        $user->update($updateData);
        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Game data updated successfully',
            'data' => [
                'gems' => $user->gems,
                'hearts' => $user->hearts,
                'unlocked_levels' => $user->unlocked_levels,
            ]
        ]);
    }

    /**
     * Update a specific field (gems, hearts, or unlocked levels)
     */
    public function updateField(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'field' => 'required|string|in:gems,hearts,unlocked_levels',
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth('api')->user();
        $field = $request->field;
        $value = $request->value;

        // Additional validation based on field type
        if ($field === 'gems' && (!is_numeric($value) || $value < 0)) {
            return response()->json([
                'success' => false,
                'message' => 'Gems must be a non-negative integer'
            ], 422);
        }

        if ($field === 'hearts' && (!is_numeric($value) || $value < 0 || $value > 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Hearts must be an integer between 0 and 5'
            ], 422);
        }

        if ($field === 'unlocked_levels' && !is_array($value)) {
            return response()->json([
                'success' => false,
                'message' => 'Unlocked levels must be an array'
            ], 422);
        }

        $user->update([$field => $value]);
        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => ucfirst($field) . ' updated successfully',
            'data' => [
                $field => $user->$field
            ]
        ]);
    }

    /**
     * Increment or decrement gems
     */
    public function modifyGems(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth('api')->user();
        $newGems = max(0, ($user->gems ?? 0) + $request->amount);
        $user->update(['gems' => $newGems]);
        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Gems updated successfully',
            'data' => [
                'gems' => $user->gems
            ]
        ]);
    }

    /**
     * Increment or decrement hearts
     */
    public function modifyHearts(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth('api')->user();
        $newHearts = max(0, min(5, ($user->hearts ?? 5) + $request->amount));
        $user->update(['hearts' => $newHearts]);
        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Hearts updated successfully',
            'data' => [
                'hearts' => $user->hearts
            ]
        ]);
    }

    /**
     * Unlock a level
     */
    public function unlockLevel(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'level' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth('api')->user();
        $unlockedLevels = $user->unlocked_levels ?? [];
        
        if (!in_array($request->level, $unlockedLevels)) {
            $unlockedLevels[] = $request->level;
            sort($unlockedLevels);
            $user->update(['unlocked_levels' => $unlockedLevels]);
            $user->refresh();
        }

        return response()->json([
            'success' => true,
            'message' => 'Level unlocked successfully',
            'data' => [
                'unlocked_levels' => $user->unlocked_levels
            ]
        ]);
    }
}

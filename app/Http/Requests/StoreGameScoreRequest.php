<?php
// app/Http/Requests/StoreGameScoreRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGameScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'player_name' => 'nullable|string|max:255',
            'score' => 'required|integer|min:0',
            'difficulty' => 'required|in:EASY,MEDIUM,HARD',
            'game_duration' => 'nullable|integer|min:0',
            'game_stats' => 'nullable|array',
            'game_stats.food_eaten' => 'nullable|integer|min:0',
            'game_stats.power_ups_collected' => 'nullable|integer|min:0',
            'game_stats.obstacles_hit' => 'nullable|integer|min:0',
            'device_id' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'score.required' => 'Score is required',
            'score.integer' => 'Score must be a valid number',
            'score.min' => 'Score cannot be negative',
            'difficulty.required' => 'Difficulty level is required',
            'difficulty.in' => 'Difficulty must be EASY, MEDIUM, or HARD',
            'user_id.exists' => 'User does not exist',
        ];
    }
}

// ==========================================


// ==========================================



// ==========================================


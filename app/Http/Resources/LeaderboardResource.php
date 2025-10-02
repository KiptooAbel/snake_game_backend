<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'player_name' => $this->player_name,
            'high_score' => $this->high_score,
            'difficulty' => $this->difficulty,
            'total_games_played' => $this->total_games_played,
            'total_score' => $this->total_score,
            'average_score' => round($this->average_score, 2),
            'user' => new UserResource($this->whenLoaded('user')),
            'rank' => $this->when(isset($this->rank), $this->rank),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leaderboard extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_name',
        'high_score',
        'difficulty',
        'total_games_played',
        'total_score',
        'average_score',
        'device_id',
        'user_id',
    ];

    protected $casts = [
        'high_score' => 'integer',
        'total_games_played' => 'integer',
        'total_score' => 'integer',
        'average_score' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', strtoupper($difficulty));
    }

    public function scopeTopPlayers($query, int $limit = 10)
    {
        return $query->orderBy('high_score', 'desc')->limit($limit);
    }

    public function updateStats(int $newScore): void
    {
        $this->total_games_played++;
        $this->total_score += $newScore;
        
        if ($newScore > $this->high_score) {
            $this->high_score = $newScore;
        }
        
        $this->average_score = $this->total_score / $this->total_games_played;
        $this->save();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameScore extends Model
{
    use HasFactory;

    protected $table = 'scores';

    protected $fillable = [
        'user_id',
        'score',
        'level',
        'game_duration',
        'difficulty',
        'game_stats',
    ];

    protected $casts = [
        'game_stats' => 'array',
        'score' => 'integer',
        'level' => 'integer',
        'game_duration' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', strtolower($difficulty));
    }

    public function scopeHighScores($query, int $limit = 10)
    {
        return $query->orderBy('score', 'desc')->limit($limit);
    }

    public function scopeRecentScores($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}

// ==========================================


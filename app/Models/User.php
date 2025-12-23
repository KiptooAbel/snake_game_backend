<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email', 
        'password',
        'first_name',
        'last_name',
        'avatar',
        'total_games',
        'best_score',
        'total_score',
        'gems',
        'hearts',
        'unlocked_levels',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'total_games' => 'integer',
            'best_score' => 'integer',
            'total_score' => 'integer',
            'gems' => 'integer',
            'hearts' => 'integer',
            'unlocked_levels' => 'array',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the user's game scores
     */
    public function gameScores()
    {
        return $this->hasMany(GameScore::class);
    }

    /**
     * Get the user's leaderboard entries
     */
    public function leaderboards()
    {
        return $this->hasMany(Leaderboard::class);
    }
}

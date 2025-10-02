# Snake Game Backend Setup Documentation

## Overview
This document provides comprehensive instructions for setting up a Laravel backend for the Snake Game **React Native** mobile application. The backend handles user authentication, score management, and leaderboards with JWT authentication for React Native integration.

## Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL 8.0 or higher
- Laravel 12.x
- React Native development environment

## Backend Features
- User registration and authentication with JWT tokens
- React Native compatible JWT authentication
- Personal score tracking
- Global leaderboards (daily, weekly, monthly)
- User profiles with avatar upload
- CORS configuration for React Native
- API rate limiting
- Input validation and sanitization
- RESTful API design

## Installation Steps

### 1. Create New Laravel Project
```bash
composer create-project laravel/laravel snake-game-backend
cd snake-game-backend
```

### 2. Install Required Packages
```bash
# JWT Authentication
composer require tymon/jwt-auth

# API Resources
composer require spatie/laravel-query-builder

# CORS support
composer require fruitcake/laravel-cors

# Rate limiting
composer require spatie/laravel-rate-limited-job-middleware
```

### 3. Environment Configuration
Create `.env` file:
```env
APP_NAME=SnakeGameAPI
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=snake_game
DB_USERNAME=your_username
DB_PASSWORD=your_password

JWT_SECRET=your-jwt-secret-key
JWT_TTL=1440

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@snakegame.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Database Setup

#### Create Database
```sql
CREATE DATABASE snake_game CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Run Migrations
```bash
php artisan migrate
```

### 5. JWT Configuration
```bash
php artisan jwt:secret
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```

## Database Schema

### Users Table
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('username')->unique();
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->string('first_name');
    $table->string('last_name');
    $table->string('avatar')->nullable();
    $table->integer('total_games')->default(0);
    $table->integer('best_score')->default(0);
    $table->integer('total_score')->default(0);
    $table->rememberToken();
    $table->timestamps();
});
```

### Scores Table
```php
Schema::create('scores', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->integer('score');
    $table->integer('level');
    $table->integer('game_duration'); // in seconds
    $table->string('difficulty')->default('normal');
    $table->json('game_stats')->nullable(); // food eaten, power-ups used, etc.
    $table->timestamps();
    
    $table->index(['user_id', 'score']);
    $table->index(['score', 'created_at']);
});
```

### Leaderboards Table (for daily/weekly/monthly)
```php
Schema::create('leaderboards', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->integer('score');
    $table->string('period_type'); // daily, weekly, monthly, all_time
    $table->date('period_date');
    $table->integer('rank');
    $table->timestamps();
    
    $table->unique(['user_id', 'period_type', 'period_date']);
    $table->index(['period_type', 'period_date', 'rank']);
});
```

## API Endpoints

### Authentication Routes
```
POST /api/auth/register     - User registration
POST /api/auth/login        - User login
POST /api/auth/logout       - User logout
POST /api/auth/refresh      - Refresh JWT token
POST /api/auth/forgot       - Password reset request
POST /api/auth/reset        - Password reset confirmation
```

### User Routes
```
GET  /api/user              - Get authenticated user profile
PUT  /api/user              - Update user profile
GET  /api/user/stats        - Get user statistics
POST /api/user/avatar       - Upload user avatar
```

### Score Routes
```
POST /api/scores            - Submit new score
GET  /api/scores            - Get user's score history
GET  /api/scores/best       - Get user's best scores
DELETE /api/scores/{id}     - Delete specific score
```

### Leaderboard Routes
```
GET  /api/leaderboard/global     - Global all-time leaderboard
GET  /api/leaderboard/daily      - Daily leaderboard
GET  /api/leaderboard/weekly     - Weekly leaderboard
GET  /api/leaderboard/monthly    - Monthly leaderboard
GET  /api/leaderboard/friends    - Friends leaderboard
```

## Model Implementations

### User Model (`app/Models/User.php`)
```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username', 'email', 'password', 'first_name', 'last_name',
        'total_games', 'best_score', 'total_score'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function leaderboardEntries()
    {
        return $this->hasMany(Leaderboard::class);
    }
}
```

### Score Model (`app/Models/Score.php`)
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'score', 'level', 'game_duration', 
        'difficulty', 'game_stats'
    ];

    protected $casts = [
        'game_stats' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

## Controller Examples

### AuthController (`app/Http/Controllers/AuthController.php`)
```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!$token = JWTAuth::attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ]);
    }
}
```

### ScoreController (`app/Http/Controllers/ScoreController.php`)
```php
<?php

namespace App\Http\Controllers;

use App\Models\Score;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScoreController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'score' => 'required|integer|min:0',
            'level' => 'required|integer|min:1',
            'game_duration' => 'required|integer|min:0',
            'difficulty' => 'required|string|in:easy,normal,hard',
            'game_stats' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth()->user();
        
        $score = Score::create([
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

        return response()->json([
            'message' => 'Score successfully recorded',
            'score' => $score,
        ], 201);
    }

    public function index()
    {
        $scores = auth()->user()
            ->scores()
            ->orderBy('score', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($scores);
    }
}
```

### LeaderboardController (`app/Http/Controllers/LeaderboardController.php`)
```php
<?php

namespace App\Http\Controllers;

use App\Models\Score;
use App\Models\User;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function global()
    {
        $leaderboard = User::select('id', 'username', 'best_score', 'total_games')
            ->where('best_score', '>', 0)
            ->orderBy('best_score', 'desc')
            ->take(100)
            ->get()
            ->map(function ($user, $index) {
                $user->rank = $index + 1;
                return $user;
            });

        return response()->json($leaderboard);
    }

    public function daily()
    {
        $today = now()->toDateString();
        
        $leaderboard = Score::with('user:id,username')
            ->whereDate('created_at', $today)
            ->selectRaw('user_id, MAX(score) as best_score')
            ->groupBy('user_id')
            ->orderBy('best_score', 'desc')
            ->take(50)
            ->get()
            ->map(function ($score, $index) {
                return [
                    'rank' => $index + 1,
                    'username' => $score->user->username,
                    'score' => $score->best_score,
                ];
            });

        return response()->json($leaderboard);
    }
}
```

## API Routes (`routes/api.php`)
```php
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
});

// Protected routes
Route::group(['middleware' => 'auth:api'], function () {
    // User routes
    Route::get('user', [UserController::class, 'profile']);
    Route::put('user', [UserController::class, 'updateProfile']);
    Route::get('user/stats', [UserController::class, 'stats']);
    
    // Score routes
    Route::resource('scores', ScoreController::class)->only(['index', 'store', 'destroy']);
    Route::get('scores/best', [ScoreController::class, 'best']);
});

// Public leaderboard routes (with rate limiting)
Route::group(['prefix' => 'leaderboard', 'middleware' => 'throttle:60,1'], function () {
    Route::get('global', [LeaderboardController::class, 'global']);
    Route::get('daily', [LeaderboardController::class, 'daily']);
    Route::get('weekly', [LeaderboardController::class, 'weekly']);
    Route::get('monthly', [LeaderboardController::class, 'monthly']);
});
```

## Middleware Configuration

### JWT Auth Guard (`config/auth.php`)
```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],

    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],
```

### CORS Configuration (`config/cors.php`)
```php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // Configure specific origins in production
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

## Deployment Instructions

### 1. Server Requirements
- PHP 8.1+ with required extensions
- MySQL 8.0+
- Nginx or Apache
- SSL certificate (Let's Encrypt recommended)

### 2. Production Environment Setup
```bash
# Clone repository
git clone https://github.com/yourusername/snake-game-backend.git
cd snake-game-backend

# Install dependencies
composer install --no-dev --optimize-autoloader

# Set permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Web Server Configuration (Nginx)
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/snake-game-backend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## React Native Integration

### API Service (`services/ApiService.js`)
```javascript
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE_URL = 'http://10.0.2.2:8000/api'; // For Android emulator
// const API_BASE_URL = 'http://localhost:8000/api'; // For iOS simulator
// const API_BASE_URL = 'https://your-production-domain.com/api'; // For production

class ApiService {
  constructor() {
    this.token = null;
  }

  async init() {
    // Load token from AsyncStorage on app start
    try {
      const token = await AsyncStorage.getItem('jwt_token');
      if (token) {
        this.token = token;
      }
    } catch (error) {
      console.error('Error loading token:', error);
    }
  }

  async setToken(token) {
    this.token = token;
    try {
      if (token) {
        await AsyncStorage.setItem('jwt_token', token);
      } else {
        await AsyncStorage.removeItem('jwt_token');
      }
    } catch (error) {
      console.error('Error saving token:', error);
    }
  }

  async clearToken() {
    this.token = null;
    try {
      await AsyncStorage.removeItem('jwt_token');
    } catch (error) {
      console.error('Error clearing token:', error);
    }
  }

  async request(endpoint, options = {}) {
    const url = `${API_BASE_URL}${endpoint}`;
    const config = {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(this.token && { Authorization: `Bearer ${this.token}` }),
      },
      ...options,
    };

    try {
      const response = await fetch(url, config);
      const data = await response.json();

      if (!response.ok) {
        if (response.status === 401) {
          // Token expired or invalid, clear it
          await this.clearToken();
        }
        throw new Error(data.message || `HTTP error! status: ${response.status}`);
      }

      return data;
    } catch (error) {
      console.error('API request failed:', error);
      throw error;
    }
  }

  // Authentication methods
  async register(userData) {
    const response = await this.request('/auth/register', {
      method: 'POST',
      body: JSON.stringify(userData),
    });
    
    if (response.success && response.token) {
      await this.setToken(response.token);
    }
    
    return response;
  }

  async login(email, password) {
    const response = await this.request('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    });
    
    if (response.success && response.token) {
      await this.setToken(response.token);
    }
    
    return response;
  }

  async logout() {
    try {
      await this.request('/auth/logout', { method: 'POST' });
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      await this.clearToken();
    }
  }

  async refreshToken() {
    const response = await this.request('/auth/refresh', { method: 'POST' });
    
    if (response.success && response.token) {
      await this.setToken(response.token);
    }
    
    return response;
  }

  async getProfile() {
    return this.request('/auth/profile');
  }

  // User methods
  async updateProfile(userData) {
    return this.request('/user', {
      method: 'PUT',
      body: JSON.stringify(userData),
    });
  }

  async getUserStats() {
    return this.request('/user/stats');
  }

  async uploadAvatar(imageUri) {
    const formData = new FormData();
    formData.append('avatar', {
      uri: imageUri,
      type: 'image/jpeg',
      name: 'avatar.jpg',
    });

    return this.request('/user/avatar', {
      method: 'POST',
      headers: {
        'Content-Type': 'multipart/form-data',
        ...(this.token && { Authorization: `Bearer ${this.token}` }),
      },
      body: formData,
    });
  }

  // Score methods
  async submitScore(scoreData) {
    return this.request('/scores', {
      method: 'POST',
      body: JSON.stringify(scoreData),
    });
  }

  async getUserScores(page = 1) {
    return this.request(`/scores?page=${page}`);
  }

  async getBestScores() {
    return this.request('/scores/best');
  }

  async deleteScore(scoreId) {
    return this.request(`/scores/${scoreId}`, {
      method: 'DELETE',
    });
  }

  // Leaderboard methods (public)
  async getGlobalLeaderboard() {
    return this.request('/leaderboard/global');
  }

  async getDailyLeaderboard() {
    return this.request('/leaderboard/daily');
  }

  async getWeeklyLeaderboard() {
    return this.request('/leaderboard/weekly');
  }

  async getMonthlyLeaderboard() {
    return this.request('/leaderboard/monthly');
  }

  async getLeaderboardByDifficulty(difficulty) {
    return this.request(`/leaderboard/difficulty/${difficulty}`);
  }

  async getHighScores(difficulty = null, limit = 10) {
    const params = new URLSearchParams();
    if (difficulty) params.append('difficulty', difficulty);
    if (limit) params.append('limit', limit.toString());
    
    return this.request(`/high-scores?${params.toString()}`);
  }
}

export default new ApiService();
```

### Usage in React Native Components

#### Authentication Hook (`hooks/useAuth.js`)
```javascript
import { useState, useEffect, useContext, createContext } from 'react';
import ApiService from '../services/ApiService';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  useEffect(() => {
    initializeAuth();
  }, []);

  const initializeAuth = async () => {
    try {
      await ApiService.init();
      if (ApiService.token) {
        const response = await ApiService.getProfile();
        if (response.success) {
          setUser(response.user);
          setIsAuthenticated(true);
        }
      }
    } catch (error) {
      console.error('Auth initialization error:', error);
      await ApiService.clearToken();
    } finally {
      setLoading(false);
    }
  };

  const login = async (email, password) => {
    try {
      const response = await ApiService.login(email, password);
      if (response.success) {
        setUser(response.user);
        setIsAuthenticated(true);
        return { success: true };
      }
      return { success: false, message: response.message };
    } catch (error) {
      return { success: false, message: error.message };
    }
  };

  const register = async (userData) => {
    try {
      const response = await ApiService.register(userData);
      if (response.success) {
        setUser(response.user);
        setIsAuthenticated(true);
        return { success: true };
      }
      return { success: false, message: response.message };
    } catch (error) {
      return { success: false, message: error.message };
    }
  };

  const logout = async () => {
    try {
      await ApiService.logout();
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      setUser(null);
      setIsAuthenticated(false);
    }
  };

  const updateProfile = async (userData) => {
    try {
      const response = await ApiService.updateProfile(userData);
      if (response.success) {
        setUser(response.user);
        return { success: true };
      }
      return { success: false, message: response.message };
    } catch (error) {
      return { success: false, message: error.message };
    }
  };

  const value = {
    user,
    loading,
    isAuthenticated,
    login,
    register,
    logout,
    updateProfile,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
```

#### Score Submission Example
```javascript
import React, { useState } from 'react';
import { View, Text, Alert } from 'react-native';
import ApiService from '../services/ApiService';

const GameOverScreen = ({ score, level, duration, difficulty, gameStats }) => {
  const [submitting, setSubmitting] = useState(false);

  const submitScore = async () => {
    if (submitting) return;
    
    setSubmitting(true);
    try {
      const response = await ApiService.submitScore({
        score,
        level,
        game_duration: duration,
        difficulty,
        game_stats: gameStats,
      });

      if (response.success) {
        Alert.alert('Success', 'Score submitted successfully!');
      } else {
        Alert.alert('Error', response.message || 'Failed to submit score');
      }
    } catch (error) {
      Alert.alert('Error', error.message || 'Network error');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <View>
      <Text>Game Over!</Text>
      <Text>Score: {score}</Text>
      <Text>Level: {level}</Text>
      <TouchableOpacity onPress={submitScore} disabled={submitting}>
        <Text>{submitting ? 'Submitting...' : 'Submit Score'}</Text>
      </TouchableOpacity>
    </View>
  );
};
```

### Required React Native Dependencies
```bash
npm install @react-native-async-storage/async-storage
npm install react-native-image-picker  # For avatar upload
```

### API Endpoints Summary

#### Authentication (JWT Required where noted)
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login  
- `POST /api/auth/logout` - User logout (JWT required)
- `POST /api/auth/refresh` - Refresh token (JWT required)
- `GET /api/auth/profile` - Get user profile (JWT required)

#### User Management (JWT Required)
- `GET /api/user` - Get user profile
- `PUT /api/user` - Update user profile
- `GET /api/user/stats` - Get user statistics
- `POST /api/user/avatar` - Upload user avatar

#### Scores (JWT Required)
- `GET /api/scores` - Get user's score history (paginated)
- `POST /api/scores` - Submit new score
- `GET /api/scores/best` - Get user's best scores by difficulty
- `GET /api/scores/{id}` - Get specific score
- `DELETE /api/scores/{id}` - Delete specific score

#### Leaderboards (Public, Rate Limited)
- `GET /api/leaderboard/global` - Global all-time leaderboard
- `GET /api/leaderboard/daily` - Daily leaderboard
- `GET /api/leaderboard/weekly` - Weekly leaderboard
- `GET /api/leaderboard/monthly` - Monthly leaderboard
- `GET /api/leaderboard/difficulty/{difficulty}` - Leaderboard by difficulty
- `GET /api/high-scores` - High scores (supports difficulty and limit params)

#### Utility
- `GET /api/health` - Health check

## Security Considerations
1. Use HTTPS in production
2. Implement proper input validation
3. Use rate limiting on all endpoints
4. Sanitize user inputs
5. Keep JWT secrets secure
6. Implement proper CORS policies
7. Use prepared statements for database queries
8. Regular security updates

## Testing
```bash
# Run tests
php artisan test

# Generate test coverage
php artisan test --coverage
```

## Monitoring and Logging
- Set up application monitoring (New Relic, DataDog)
- Configure log rotation
- Monitor API response times
- Set up error tracking (Sentry)

## Backup Strategy
- Daily database backups
- Weekly full application backups
- Off-site backup storage
- Automated backup verification

This comprehensive setup will provide a robust backend for your Snake Game with all the features needed for user management, scoring, and leaderboards.

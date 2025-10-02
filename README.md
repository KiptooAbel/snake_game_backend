# Snake Game API - React Native Backend

A complete Laravel-based REST API for the Snake Game React Native mobile application, featuring JWT authentication, score tracking, and leaderboards.

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- MySQL 8.0+
- XAMPP/WAMP or similar local development environment

### Installation

1. **Clone and Setup**
   ```bash
   git clone <your-repo-url>
   cd snake-game-api
   composer install
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret
   ```

3. **Database Setup**
   - Create a MySQL database named `snake_game`
   - Update your `.env` file with database credentials:
   ```env
   DB_DATABASE=snake_game
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

4. **Run Migrations and Seed Data**
   ```bash
   php artisan migrate:fresh
   php artisan db:seed --class=TestDataSeeder
   ```

5. **Start Development Server**
   ```bash
   php artisan serve
   ```

API will be available at `http://localhost:8000/api`

## 📱 React Native Integration

### Required Packages
```bash
npm install @react-native-async-storage/async-storage
npm install react-native-image-picker  # For avatar uploads
```

### Base API Configuration
For React Native development, use these base URLs:

- **Android Emulator**: `http://10.0.2.2:8000/api`
- **iOS Simulator**: `http://localhost:8000/api`
- **Physical Device**: `http://YOUR_LOCAL_IP:8000/api` (e.g., `http://192.168.1.100:8000/api`)

## 🔐 Authentication Flow

### User Registration
```javascript
const response = await ApiService.register({
  username: 'johndoe',
  email: 'john@example.com',
  password: 'password123',
  password_confirmation: 'password123',
  first_name: 'John',
  last_name: 'Doe'
});
```

### User Login
```javascript
const response = await ApiService.login('john@example.com', 'password123');
// Token is automatically stored in AsyncStorage
```

## 🎮 Game Integration

### Submit Score After Game
```javascript
const submitScore = async (gameData) => {
  try {
    const response = await ApiService.submitScore({
      score: gameData.finalScore,
      level: gameData.levelReached,
      game_duration: gameData.playTimeSeconds,
      difficulty: gameData.difficulty, // 'easy', 'normal', 'hard'
      game_stats: {
        food_eaten: gameData.foodCount,
        power_ups_used: gameData.powerUpsUsed,
        walls_hit: gameData.collisions
      }
    });
    
    if (response.success) {
      console.log('Score submitted successfully!');
    }
  } catch (error) {
    console.error('Failed to submit score:', error);
  }
};
```

## 📊 API Endpoints

### Authentication
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/auth/register` | Register new user | ❌ |
| POST | `/auth/login` | User login | ❌ |
| POST | `/auth/logout` | User logout | ✅ |
| POST | `/auth/refresh` | Refresh JWT token | ✅ |
| GET | `/auth/profile` | Get user profile | ✅ |

### User Management
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/user` | Get user profile | ✅ |
| PUT | `/user` | Update user profile | ✅ |
| GET | `/user/stats` | Get user statistics | ✅ |
| POST | `/user/avatar` | Upload avatar | ✅ |

### Scores
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/scores` | Get user's score history | ✅ |
| POST | `/scores` | Submit new score | ✅ |
| GET | `/scores/best` | Get user's best scores | ✅ |
| DELETE | `/scores/{id}` | Delete score | ✅ |

### Leaderboards (Public)
| Method | Endpoint | Description | Rate Limited |
|--------|----------|-------------|--------------|
| GET | `/leaderboard/global` | Global leaderboard | ✅ |
| GET | `/leaderboard/daily` | Daily leaderboard | ✅ |
| GET | `/leaderboard/weekly` | Weekly leaderboard | ✅ |
| GET | `/leaderboard/monthly` | Monthly leaderboard | ✅ |
| GET | `/high-scores` | High scores with params | ✅ |

## 🧪 Testing

### Test User Accounts
| Email | Password | Username |
|-------|----------|----------|
| test1@example.com | password | testuser1 |
| test2@example.com | password | testuser2 |
| test3@example.com | password | testuser3 |

### API Health Check
```bash
curl http://localhost:8000/api/health
```

## 🔧 React Native Usage

Check the `BACKEND_SETUP.md` file for complete React Native integration examples including:

- Complete `ApiService.js` implementation
- Authentication hook (`useAuth.js`)
- Score submission examples
- Leaderboard integration
- Error handling patterns

## 🚀 Production Deployment

1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Configure proper CORS origins
3. Use HTTPS in production
4. Set up proper database credentials
5. Configure rate limiting and monitoring

For detailed setup instructions, see `BACKEND_SETUP.md`.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
#   s n a k e _ g a m e _ b a c k e n d  
 
@echo off
echo Setting up Snake Game Laravel Backend...
echo.

cd "c:\xampp\htdocs\snake\Laravel Backend"

echo Step 1: Installing Composer dependencies...
composer install --optimize-autoloader --no-dev
echo.

echo Step 2: Setting up environment...
if not exist .env copy .env.example .env
echo.

echo Step 3: Generating application key...
php artisan key:generate
echo.

echo Step 4: Setting up JWT secret...
php artisan jwt:secret --force
echo.

echo Step 5: Running database migrations...
php artisan migrate --force
echo.

echo Step 6: Clearing caches...
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo.

echo Step 7: Optimizing for production...
php artisan config:cache
php artisan route:cache
echo.

echo Setup complete!
echo.
echo Backend URL: http://localhost/snake-api/public
echo API Base URL: http://localhost/snake-api/public/api
echo Health Check: http://localhost/snake-api/public/api/health
echo.
pause

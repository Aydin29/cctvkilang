@echo off
echo Preparing fast-start with SQLite (no MySQL required)...

if not exist database\database.sqlite (
    echo - Creating SQLite DB file
    type nul > database\database.sqlite
)

echo Generating optimized autoload and caching
where composer >nul 2>&1
if %errorlevel%==0 (
    composer dump-autoload -o
) else (
    if exist composer.phar (
        php composer.phar dump-autoload -o
    )
)
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo Starting PHP built-in server with environment variables for SQLite
set DB_CONNECTION=sqlite
set DB_DATABASE=%CD%\database\database.sqlite
php -d opcache.enable=1 -d opcache.revalidate_freq=0 -S 127.0.0.1:8000 -t public

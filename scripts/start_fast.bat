@echo off
echo Starting fast-start routine...

echo 1) Generating optimized autoload (composer dump-autoload -o)
where composer >nul 2>&1
if %errorlevel%==0 (
    composer dump-autoload -o
) else (
    if exist composer.phar (
        php composer.phar dump-autoload -o
    ) else (
        echo Composer not found in PATH and composer.phar missing. Skipping dump-autoload.
    )
)

echo 2) Caching config, routes and views
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo 3) Starting PHP built-in server with OPcache enabled
php -d opcache.enable=1 -d opcache.revalidate_freq=0 -S 127.0.0.1:8000 -t public

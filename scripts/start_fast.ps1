Param(
    [string]$Host = "127.0.0.1",
    [int]$Port = 8000
)

Write-Host "Starting fast-start routine..."

# 1) Optimized composer autoload
Write-Host "- Generating optimized autoload (composer dump-autoload -o)"
if (Test-Path ".\composer.phar") {
    php .\composer.phar dump-autoload -o
} elseif (Get-Command composer -ErrorAction SilentlyContinue) {
    composer dump-autoload -o
} else {
    Write-Warning "Composer not found in PATH and composer.phar missing. Skipping dump-autoload."
}

# 2) Laravel caches
Write-Host "- Caching config, routes and views"
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3) Start PHP built-in server with OPcache enabled for faster request handling
Write-Host "- Launching PHP built-in server with OPcache enabled (this process will run in foreground)"
$opcacheArgs = "-d opcache.enable=1 -d opcache.revalidate_freq=0 -S $Host`:$Port -t public"
Write-Host "php $opcacheArgs"
& php $opcacheArgs

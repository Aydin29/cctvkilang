Param(
    [string]$Host = "127.0.0.1",
    [int]$Port = 8000
)

Write-Host "Preparing fast-start with SQLite (no MySQL required)..."

$dbPath = Join-Path -Path (Get-Location) -ChildPath 'database\database.sqlite'
if (-not (Test-Path $dbPath)) {
    Write-Host "- Creating SQLite DB file at $dbPath"
    New-Item -ItemType File -Path $dbPath | Out-Null
}

Write-Host "- Generating optimized autoload and caching"
if (Get-Command composer -ErrorAction SilentlyContinue) { composer dump-autoload -o } elseif (Test-Path .\composer.phar) { php .\composer.phar dump-autoload -o }
php artisan config:cache
php artisan route:cache
php artisan view:cache

Write-Host "- Starting PHP built-in server with environment variables for SQLite"
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = $dbPath
$opcacheArgs = "-d opcache.enable=1 -d opcache.revalidate_freq=0 -S $Host`:$Port -t public"
Write-Host "php $opcacheArgs (with DB_CONNECTION=sqlite)"
& php $opcacheArgs

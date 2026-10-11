Summary
-------
Small helper scripts to reduce Laravel startup time before running the development server.

Files
-----
- `scripts/start_fast.ps1` — PowerShell script: runs `composer dump-autoload -o`, caches config/routes/views, then starts PHP built-in server with OPcache enabled.
- `scripts/start_fast.bat` — Windows batch version doing the same steps.

Notes & Recommendations
-----------------------
- Disabling Xdebug will significantly speed up PHP execution. Edit your `php.ini` (e.g., `C:\xampp\php\php.ini`) and comment out any `zend_extension="xdebug.dll"` lines, then restart Apache/PHP.
- Running `composer dump-autoload -o` and caching (`php artisan config:cache`, `route:cache`, `view:cache`) reduces runtime bootstrap cost.
- These scripts cannot absolutely guarantee a 5-second startup in all environments — hardware, installed PHP extensions, and Composer autoload size affect startup time. They do however apply the usual optimizations.

Usage
-----
PowerShell:

    cd path\to\project
    ./scripts/start_fast.ps1

Batch (Windows cmd):

    cd path\to\project
    scripts\start_fast.bat

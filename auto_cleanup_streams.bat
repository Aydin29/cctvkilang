@echo off
title CCTV Auto Cleanup Worker
chcp 65001 >nul

cd /d "C:\xampp\htdocs\cctvkilang"

echo ============================================================
echo Menjalankan Worker Pembersih Otomatis Folder Stream (10 Menit)
echo ============================================================

:loop
php artisan stream:cleanup --no-delete-unused-folders
REM Tunggu 600 detik (10 Menit) sebelum melakukan pembersihan lagi
timeout /t 600 /nobreak >nul
goto loop
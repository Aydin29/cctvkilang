
@echo off
setlocal EnableExtensions EnableDelayedExpansion

REM Start Laravel in background, then launch per-slug start_stream.bat in parallel
set "WORKDIR=C:\xampp\htdocs\cctvkilang"
set "HOST=127.0.0.1"
set "PORT=8000"
set "STREAM_ROOT=%WORKDIR%\public\stream"

cd /d "%WORKDIR%"
echo ============================================================
echo Starting Laravel (serve) on http://%HOST%:%PORT%
echo ============================================================

start "Laravel Serve" /B /LOW cmd /c "cd /d "%WORKDIR%" && php artisan serve --host=%HOST% --port=%PORT%"

timeout /t 2 /nobreak >nul

echo ============================================================
echo Launching start_stream.bat for each slug under %STREAM_ROOT%
echo ============================================================

for /d %%D in ("%STREAM_ROOT%\*") do (
    if exist "%%~fD\start_stream.bat" (
        echo [START] %%~nxD
        start "" /B cmd /c "cd /d "%%~fD" && call "%%~fD\start_stream.bat" > "%%~fD\ffmpeg_launcher.log" 2>&1"
        timeout /t 1 /nobreak >nul
    ) else (
        echo [SKIP] No start_stream.bat in %%~nxD
    )
)

echo ============================================================
echo Starting auto cleanup worker: auto_cleanup_streams.bat
echo ============================================================


start "Auto Cleanup Worker" /B cmd /c "cd /d "%WORKDIR%" && call "%WORKDIR%\auto_cleanup_streams.bat" > "%WORKDIR%\auto_cleanup_worker.log" 2>&1"

REM Saat bat ini dihentikan (Ctrl+C/close), proses per-slug seharusnya tetap berjalan.
REM (start_serve_and_streams.bat hanya start semua ffmpeg, bukan mengontrol stop StartStream otomatis)
REM Kita set stop.flag untuk semua slug sehingga start_stream.bat yang dibuat PHP akan keluar dari :loop.
REM Catatan: stop.flag akan dicek oleh start_stream.bat di folder public\stream\<slug>.

echo.
REM stop.flag dibuat oleh script per-slug (kalau memang dipakai).
REM Versi ini: start_serve_and_streams.bat tidak menulis stop.flag global saat dimatikan.
REM Namun, saat batch masih jalan, CCTV baru yang dibuat kemungkinan BELUM ikut serta,
REM karena loop start_serve_and_streams.bat hanya berjalan saat ini (sekali eksekusi).


echo DONE. You should now have playlist.m3u8 and playlist_*.ts in each public\stream\<slug> folder, plus ffmpeg.log.
echo To fully stop immediately: close this window/cmd, then FFmpeg per-slug should exit due to stop.flag.
pause


endlocal



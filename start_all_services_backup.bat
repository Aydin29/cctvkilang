@echo off
echo Starting CCTV Streaming System...
echo.

:: Start PHP Artisan Server
echo Starting PHP Artisan Server...
start "PHP Artisan Server" /B php artisan serve
timeout /t 5 /nobreak >nul
echo PHP Artisan Server started.
echo.

:: Start FFmpeg Streams
echo Starting FFmpeg Streams...

:: TEST
echo Starting stream for TEST...
cd /d "C:\xampp\htdocs\cctvkilang\public\stream\TEST"
start "CCTV TEST" /B ffmpeg -rtsp_transport tcp -i "rtsp://admin:Password.123@10.56.236.43:554/live1s1.sdp" -c:v libx264 -preset ultrafast -tune zerolatency -f hls -hls_time 1 -hls_list_size 3 -hls_flags delete_segments playlist.m3u8 > ffmpeg_output.log 2>&1

echo FFmpeg Streams started.
echo.

echo All services started successfully!
echo.
echo To stop the services, close the command prompt windows or use Task Manager to end the processes.
echo.
pause

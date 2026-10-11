@echo off
setlocal EnableExtensions EnableDelayedExpansion

REM ============================================================
REM Start FFmpeg for every folder inside public\stream\<slug>
REM - Each folder will get its own:
REM     ffmpeg.log
REM     playlist.m3u8
REM     playlist_*.ts
REM ============================================================

set "WORKDIR=C:\xampp\htdocs\cctvkilang"
set "FFMPEG_EXE=C:\ffmpeg\bin\ffmpeg.exe"
set "STREAM_ROOT=%WORKDIR%\public\stream"

REM RTSP URL file is optional. If present, it will be used.
REM Generate_cctv_list.php currently writes: temp_worker_url_<slug>.txt in WORKDIR
REM but each folder-specific RTSP can be supplied by setting URL_FILE inside loop.

if not exist "%FFMPEG_EXE%" (
  echo ERROR: FFmpeg not found: "%FFMPEG_EXE%"
  pause
  exit /b 1
)

if not exist "%STREAM_ROOT%" (
  echo ERROR: Stream root not found: "%STREAM_ROOT%"
  pause
  exit /b 1
)


echo ============================================================
echo Starting FFmpeg in PARALLEL for all folders in %STREAM_ROOT%
echo ============================================================

REM Generate CCTV list so RTSP temp files exist (if your setup needs it)
REM IMPORTANT: your generate_cctv_list.php prints to STDOUT; your other scripts create temp_worker_url_<slug>.txt.
REM Here we generate those temp files directly to guarantee ffmpeg.log/playlist will start.
if exist "%WORKDIR%\generate_cctv_list.php" (
  echo Generating temp_worker_url_*.txt from generate_cctv_list.php...
  php "%WORKDIR%\generate_cctv_list.php" > "%WORKDIR%\temp_cctvs_runtime.txt"

  for /f "tokens=1,2,3 delims=|" %%A in ("%WORKDIR%\temp_cctvs_runtime.txt") do (
    set "SLUG=%%A"
    set "URL=%%C"

    REM slug-safe conversion for RTSP filename used by generator scripts
    set "SLUG_SAFE=!SLUG!"
    set "SLUG_SAFE=!SLUG_SAFE: =_!"
    set "SLUG_SAFE=!SLUG_SAFE:.=_!"
    set "SLUG_SAFE=!SLUG_SAFE:-=_!"
    set "SLUG_SAFE=!SLUG_SAFE:/=_!"
    set "SLUG_SAFE=!SLUG_SAFE:\\=_!"

    set "URL_FILE=%WORKDIR%\temp_worker_url_!SLUG_SAFE!.txt"
    (echo !URL!)>"!URL_FILE!"
  )

  del /q "%WORKDIR%\temp_cctvs_runtime.txt" >nul 2>nul
)

echo.

for /d %%D in ("%STREAM_ROOT%"\*) do (
  set "SLUG=%%~nxD"

  if "!SLUG!"=="" goto :continue

  REM debug trace
  echo [INFO] Folder slug: "!SLUG!"


  set "OUT_DIR=%%~fD"
  set "FFMPEG_LOG=!OUT_DIR!\ffmpeg.log"

  REM slug-safe conversion for RTSP filename used by your generator
  set "SLUG_SAFE=!SLUG!"
  set "SLUG_SAFE=!SLUG_SAFE: =_!"
  set "SLUG_SAFE=!SLUG_SAFE:.=_!"
  set "SLUG_SAFE=!SLUG_SAFE:-=_!"
  set "SLUG_SAFE=!SLUG_SAFE:/=_!"
  set "SLUG_SAFE=!SLUG_SAFE:\\=_!"

  set "URL_FILE=%WORKDIR%\temp_worker_url_!SLUG_SAFE!.txt"

  if not exist "!URL_FILE!" (
    echo SKIP: URL file not found for slug "!SLUG!": "!URL_FILE!"
    goto :continue
  )

  for /f "usebackq delims=" %%U in ("!URL_FILE!") do set "RTSP_URL=%%U"
  if "!RTSP_URL!"=="" (
    echo SKIP: Empty RTSP URL for slug "!SLUG!"
    goto :continue
  )

  echo ------------------------------------------------------------------------------
  echo Starting: "!SLUG!"
  echo   OUT_DIR: !OUT_DIR!
  echo   URL_FILE: !URL_FILE!
  echo   LOG: !FFMPEG_LOG!
  echo ------------------------------------------------------------------------------

  REM clear old playlist artifacts in this folder
  del /q "!OUT_DIR!\playlist_*.ts" >nul 2>nul
  if exist "!OUT_DIR!\playlist.m3u8" del /q "!OUT_DIR!\playlist.m3u8" >nul 2>nul
  if exist "!FFMPEG_LOG!" del /q "!FFMPEG_LOG!" >nul 2>nul

  REM Start ffmpeg in background (one process per folder)
  REM Note: Use ^&& to escape for cmd /c parsing
  start "FFmpeg - !SLUG!" /B /LOW cmd /c "cd /d \"!OUT_DIR!\" ^&^& (
    echo Starting ffmpeg for !SLUG! > \"!FFMPEG_LOG!\" 2>&1
    ^&^& \"%FFMPEG_EXE%\" -hide_banner -loglevel warning -rtsp_transport tcp -analyzeduration 5000000 -probesize 5000000 -use_wallclock_as_timestamps 1 -fflags +nobuffer+genpts+discardcorrupt -flags low_delay -i \"!RTSP_URL!\" -c:v libx264 -preset ultrafast -tune zerolatency -f hls -hls_time 1 -hls_list_size 3 -hls_flags delete_segments -hls_segment_filename \"playlist_%%d.ts\" -y \"playlist.m3u8\" >> \"!FFMPEG_LOG!\" 2>&1
  )"

  :continue
)

echo.
echo DONE. You should now have playlist.m3u8 and playlist_*.ts in each public\stream\<slug> folder, plus ffmpeg.log.
echo To stop all FFmpeg processes: taskkill /F /IM ffmpeg.exe
pause


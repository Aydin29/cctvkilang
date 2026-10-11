@echo off
setlocal EnableExtensions

REM Wrapper to start ffmpeg for a single slug with safe argument parsing
REM Args:
REM   %1 = rtspUrl
REM   %2 = outputPath (playlist.m3u8)

if "%~1"=="" (
  echo ERROR: missing rtspUrl
  exit /b 1
)

if "%~2"=="" (
  echo ERROR: missing outputPath
  exit /b 1
)

set "WORKDIR=C:\xampp\htdocs\cctvkilang"
set "FFMPEG_PS1=%WORKDIR%\run_ffmpeg_playlist_enhanced.ps1"

if not exist "%FFMPEG_PS1%" (
  echo ERROR: missing %FFMPEG_PS1%
  exit /b 1
)

REM Create output dir if needed is handled by the PS script
REM Start ffmpeg and redirect stdout/stderr inside wrapper log file
powershell -NoProfile -ExecutionPolicy Bypass -File "%FFMPEG_PS1%" -rtspUrl "%~1" -outputPath "%~2" *>> "%~dp2ffmpeg.log"


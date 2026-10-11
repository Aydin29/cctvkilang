@echo off
setlocal EnableExtensions EnableDelayedExpansion
set "STREAM_ROOT=C:\xampp\htdocs\cctvkilang\public\stream"
echo DEBUG START
for /d %%D in ("%STREAM_ROOT%\*") do (
    set "SLUG=%%~nxD"
    echo SLUG=!SLUG!
)
echo DEBUG END
endlocal

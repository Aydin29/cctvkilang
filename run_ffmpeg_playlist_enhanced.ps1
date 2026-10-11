param(
    [string]$rtspUrl,
    [string]$outputPath,
    # hls_time per segmen (detik)
    [int]$hlsTime = 2,
    # sliding window: playlist duration kira-kira hls_time * hlsListSize
    # target: 1 jam => 3600 detik
    [int]$hlsListSize = 1800
)


# Path to FFmpeg executable
$ffmpegPath = "C:\ffmpeg\bin\ffmpeg.exe"

# Extract directory from output path
if ([string]::IsNullOrWhiteSpace($outputPath)) {
    throw "Missing required parameter: outputPath"
}
$outputDir = [System.IO.Path]::GetDirectoryName($outputPath)
if ([string]::IsNullOrWhiteSpace($outputDir)) {
    throw "Invalid outputPath (cannot determine directory): $outputPath"
}


# Ensure output directory exists
if (!(Test-Path $outputDir)) {
    New-Item -ItemType Directory -Path $outputDir -Force | Out-Null
}

# Determine CPU thread limit to target maximum 75% usage
$cpuCount = (Get-CimInstance Win32_Processor | Measure-Object -Property NumberOfLogicalProcessors -Sum).Sum
if (-not $cpuCount -or $cpuCount -lt 1) {
    $cpuCount = 1
}
$maxThreads = [math]::Max(1, [math]::Floor($cpuCount * 0.75))

# Log file path
$logFile = "$outputDir\ffmpeg.log"

# Build FFmpeg command
$ffmpegArgs = @(
    "-rtsp_transport", "tcp",
    "-i", "`"$rtspUrl`"",
    "-c:v", "libx264",
    "-preset", "superfast",
    "-tune", "zerolatency",
    "-r", "25",
    "-threads", "$maxThreads",
    "-c:a", "aac",
    "-b:a", "96k",
    "-pix_fmt", "yuv420p",
    "-f", "hls",
    "-hls_time", $hlsTime,
    "-hls_list_size", $hlsListSize,
    "-hls_flags", "delete_segments+omit_endlist",
    "-start_number", "1",
    "-y",
    "`"$outputPath`""
)

# Start FFmpeg process with low priority and redirect output to log file
# Start-Process -Priority is not available on some PowerShell versions.
# Lower priority where supported; if not, the script will still run.
try {
    $process = Start-Process -FilePath $ffmpegPath -ArgumentList $ffmpegArgs -NoNewWindow -PassThru `
        -RedirectStandardOutput $logFile -RedirectStandardError $logFile -Priority BelowNormal
} catch {
    $process = Start-Process -FilePath $ffmpegPath -ArgumentList $ffmpegArgs -NoNewWindow -PassThru `
        -RedirectStandardOutput $logFile -RedirectStandardError $logFile
}


# Write PID to file for tracking
$pidFile = "$outputDir\ffmpeg.pid"
$process.Id | Out-File -FilePath $pidFile

Write-Host "FFmpeg started with PID: $($process.Id) for $rtspUrl"
Write-Host "Output directory: $outputDir"
Write-Host "Log file: $logFile"

<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use App\Services\StreamConversionService;
use Illuminate\Support\Str;
use function rawurlencode;

class StreamController extends Controller
{
    /**
     * @var StreamConversionService
     */
    protected $streamConversionService;

    /**
     * StreamController constructor.
     *
     * @param StreamConversionService $streamConversionService
     */
    public function __construct(StreamConversionService $streamConversionService)
    {
        $this->streamConversionService = $streamConversionService;
    }

    /**
     * Memulai proses streaming untuk CCTV berdasarkan slug.
     *
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function start($slug)
    {
        // Decode URL-encoded slug (spaces become %20 in URLs)
        $decodedSlug = urldecode($slug);
        $cctv = Cctv::where('slug', $decodedSlug)->first();

        if (!$cctv) {
            return response()->json(['error' => 'CCTV stream not found for this slug'], 404);
        }

        // Panggil service untuk memulai proses konversi menggunakan ID CCTV
        $result = $this->streamConversionService->startStream($cctv->id);

        if (!$result) {
            return response()->json([
                'message' => 'Failed to start stream',
                'error' => 'Could not run ffmpeg command.'
            ], 500);
        }

        return response()->json([
            'message' => 'Stream started successfully',
            'playlist_url' => asset('stream/' . rawurlencode($cctv->slug) . '/playlist.m3u8'),
        ]);
    }

    /**
     * Melayani file playlist HLS (m3u8) dari direktori stream.
     *
     * @param string $slug
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function servePlaylist($slug)
    {
        // Decode URL-encoded slug (spaces become %20 in URLs)
        $decodedSlug = urldecode($slug);
        $cctv = Cctv::where('slug', $decodedSlug)->firstOrFail();
        $path = public_path("stream/" . $cctv->slug . "/playlist.m3u8");

        // Check if file exists immediately
        if (!File::exists($path)) {
            abort(404, "Stream playlist not available. Please try again in a moment.");
        }

        return Response::file($path, [
            'Content-Type' => 'application/vnd.apple.mpegurl',
        ]);
    }

    /**
     * Melayani file segment HLS (.ts, .m3u8) dari direktori stream.
     *
     * @param string $slug
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function serveSegment($slug, $filename)
    {
        // Decode URL-encoded slug (spaces become %20 in URLs)
        $decodedSlug = urldecode($slug);
        $cctv = Cctv::where('slug', $decodedSlug)->firstOrFail();
        
        // Build full path to the requested file
        $path = public_path("stream/" . $cctv->slug . "/" . $filename);
        
        // Security check: ensure path is within stream directory
        $streamDir = public_path("stream/" . $cctv->slug);
        if (!str_starts_with(realpath($path) ?: $path, realpath($streamDir) ?: $streamDir)) {
            abort(403, "Access denied.");
        }

        // Check if file exists
        if (!File::exists($path)) {
            abort(404, "Segment file not found: {$filename}");
        }

        // Determine MIME type based on file extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $mimeType = match($extension) {
            'ts' => 'video/mp2t',
            'm3u8' => 'application/vnd.apple.mpegurl',
            default => File::mimeType($path) ?? 'application/octet-stream',
        };

        return Response::file($path, [
            'Content-Type' => $mimeType,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cctv;
use App\Services\StreamConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CctvStatusBatchController extends Controller
{
    public function pingAll(Request $request)
    {
        $timeoutSeconds = (int) ($request->input('timeout', 5));
        $timeoutSeconds = max(1, min($timeoutSeconds, 20));

        // ambil semua CCTV yang punya ip
        $cctvs = Cctv::query()
            ->whereNotNull('ip_address')
            ->get(['id', 'name', 'slug', 'ip_address', 'status', 'stream_url']);

        $total = $cctvs->count();
        $online = 0;
        $offline = 0;

        /** @var StreamConversionService $streamService */
        $streamService = app(StreamConversionService::class);

        foreach ($cctvs as $cctv) {
            try {
                // Kalau stream_url kosong, paksa OFFLINE
                if (empty($cctv->stream_url)) {
                    $cctv->update(['status' => 'offline']);
                    $offline++;
                    continue;
                }

                // Reuse method testConnection yang sudah ada (ffprobe)
                // Catatan: StreamConversionService::testConnection(Cctv $cctv) pakai stream_url
                // dan tidak menerima timeout, tapi minimal logic-nya konsisten.
                $ok = $streamService->testConnection($cctv);

                $newStatus = $ok ? 'online' : 'offline';
                $cctv->update(['status' => $newStatus]);

                if ($ok) {
                    $online++;
                } else {
                    $offline++;
                }
            } catch (\Throwable $e) {
                Log::error('pingAll failed for cctv_id=' . $cctv->id . ': ' . $e->getMessage());
                $cctv->update(['status' => 'offline']);
                $offline++;
            }
        }

        return response()->json([
            'success' => true,
            'total' => $total,
            'online' => $online,
            'offline' => $offline,
            'timestamp' => now()->toISOString(),
        ]);
    }
}



<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CctvStatusService;
use Illuminate\Http\Request;

class CctvIpController extends Controller
{
    /**
     * Ping CCTV by IP (TCP connect) to determine online/offline.
     *
     * Expects JSON body:
     * - ip: string
     * - port: int (optional)
     * - timeout: int (optional)
     */
    public function testIpConnection(Request $request)
    {
        $ip = $request->input('ip');
        $port = (int) $request->input('port', 80);
        $timeout = (int) $request->input('timeout', 5);

        if (!$ip || !is_string($ip)) {
            return response()->json(['success' => false, 'error' => 'IP tidak valid'], 400);
        }

        /** @var CctvStatusService $service */
        $service = app(CctvStatusService::class);
        $ok = $service->pingCctv($ip, $port, $timeout);

        return response()->json(['success' => $ok]);
    }
}


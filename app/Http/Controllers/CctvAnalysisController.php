<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use App\cctvanalys\rtsp\RtspAnalysisService;
use App\cctvanalys\hls\HlsAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CctvAnalysisController extends Controller
{
    protected $rtspService;
    protected $hlsService;

    public function __construct(
        RtspAnalysisService $rtspService,
        HlsAnalysisService $hlsService
    ) {
        $this->rtspService = $rtspService;
        $this->hlsService = $hlsService;
    }

    /**
     * Display the CCTV analysis dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $cctvs = Cctv::paginate(12); // Paginate with 12 items per page
        return view('cctvanalys.index', compact('cctvs'));
    }

    /**
     * Analyze a specific CCTV.
     *
     * @param Cctv $cctv
     * @return \Illuminate\View\View
     */
    public function analyze(Cctv $cctv)
    {
        // Perform RTSP and HLS analysis only
        $rtspAnalysis = $this->rtspService->analyze($cctv);
        $hlsAnalysis = $this->hlsService->analyze($cctv);

        return view('cctvanalys.show', compact('cctv', 'rtspAnalysis', 'hlsAnalysis'));
    }

    /**
     * Perform analysis based on CCTV type.
     *
     * @param Cctv $cctv
     * @return array
     */
    private function performAnalysis(Cctv $cctv): array
    {
        // Determine analysis type based on streaming status and configuration
        if ($cctv->is_streaming && !empty($cctv->playlist_url)) {
            // HLS streaming
            return $this->hlsService->analyze($cctv);
        } else {
            // RTSP capable
            return $this->rtspService->analyze($cctv);
        }
    }

    /**
     * Get analysis data via AJAX.
     *
     * @param Cctv $cctv
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAnalysisData(Cctv $cctv)
    {
        try {
            // Biar query ringan: cache hasil analisis per CCTV (per ~55 detik)
            $cacheTtlSeconds = 55;
            $cacheKey = 'cctv_analysis:' . $cctv->id . ':' . now()->format('YmdHi');

            $payload = cache()->remember($cacheKey, $cacheTtlSeconds, function () use ($cctv) {
                $rtspAnalysis = $this->rtspService->analyze($cctv);
                $hlsAnalysis = $this->hlsService->analyze($cctv);

                return [
                    'rtsp' => $rtspAnalysis,
                    'hls' => $hlsAnalysis,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $payload
            ]);

        } catch (\Exception $e) {
            Log::error("Error getting analysis data for CCTV {$cctv->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to get analysis data'
            ], 500);
        }
    }

    /**
     * Get metadata for a CCTV.
     *
     * @param Cctv $cctv
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMetadata(Cctv $cctv)
    {
        try {
            $metadata = [];

            if ($cctv->is_streaming && !empty($cctv->playlist_url)) {
                $metadata = $this->hlsService->getMetadata($cctv);
            } elseif (!empty($cctv->rtsp_url)) {
                $metadata = $this->rtspService->getMetadata($cctv);
            }

            return response()->json([
                'success' => true,
                'metadata' => $metadata
            ]);
        } catch (\Exception $e) {
            Log::error("Error getting metadata for CCTV {$cctv->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to get metadata'
            ], 500);
        }
    }

    /**
     * Store a snapshot of the current analysis as a report.
     *
     * Expects JSON body containing:
     *  - protocol: string (rtsp|hls)
     *  - metrics: object with delay,jitter,packet_loss,throughput values
     *  - chart_data: object with labels and datasets (exact shape from Chart.js)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Cctv $cctv
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeReport(Request $request, Cctv $cctv)
    {
        $validated = $request->validate([
            'protocol' => 'required|string|in:rtsp,hls',
            'metrics' => 'required|array',
            'chart_data' => 'required|array',
        ]);

        try {
            $report = \App\Models\AnalysisReport::create([
                'cctv_id' => $cctv->id,
                'protocol' => $validated['protocol'],
                'metrics' => $validated['metrics'],
                'chart_data' => $validated['chart_data'],
            ]);

            return response()->json(['success' => true, 'report_id' => $report->id]);
        } catch (\Exception $e) {
            Log::error("Failed to store analysis report for CCTV {$cctv->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Unable to save report'], 500);
        }
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use App\Services\StreamConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function rawurlencode;

class CctvController extends Controller
{
    /**
     * @var StreamConversionService
     */
    protected StreamConversionService $streamService;

    /**
     * CctvController constructor.
     *
     * @param StreamConversionService $streamService
     */
    public function __construct(StreamConversionService $streamService)
    {
        $this->streamService = $streamService;
    }

    /**
     * Get a list of supported camera brands and their RTSP paths.
     *
     * @return array
     */
    private function getCameraBrands(): array
    {
        return [
            'hikvision' => [
                'main_stream' => '/Streaming/Channels/101',
                'sub_stream' => '/Streaming/Channels/102',
            ],
            'dahua' => [
                'main_stream' => '/cam/realmonitor?channel=1&subtype=0',
                'sub_stream' => '/cam/realmonitor?channel=1&subtype=1',
            ],
            'axis' => [
                'main_stream' => '/axis-media/media.amp',
                'sub_stream' => '/axis-media/media.amp?resolution=320x240',
            ],
            'foscam' => [
                'main_stream' => '/videoMain',
                'sub_stream' => '/videoSub',
            ],
            'honeywell' => [
                'main_stream' => '/live1s1.sdp',
                'sub_stream' => '/live2s1.sdp',
            ],
            'generic' => [
                'main_stream' => '/stream1',
                'sub_stream' => '/stream2',
            ],
        ];
    }

    /**
     * Display a listing of the CCTV cameras.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $currentFilter = $request->input('status', '');
        $currentSearch = $request->input('search', '');
        $currentLocation = $request->input('location', '');

        $query = Cctv::query();

        if ($currentFilter) {
            $query->where('status', $currentFilter);
        }
        
        if ($currentLocation) {
            $query->where('location', $currentLocation);
        }

        if ($currentSearch) {
            $query->where(function ($q) use ($currentSearch) {
                $q->where('name', 'like', "%$currentSearch%")
                    ->orWhere('location', 'like', "%$currentSearch%")
                    ->orWhere('ip_address', 'like', "%$currentSearch%");
            });
        }

        $cctvs = $query->paginate(15); // Implement pagination
        $statistics = Cctv::getStatistics();
        // Cache locations for 1 hour to reduce database queries
        $locations = cache()->remember('cctv_locations', 3600, function () {
            return Cctv::select('location')->distinct()->pluck('location');
        });

        return view('cctvs.index', compact('cctvs', 'statistics', 'currentFilter', 'currentSearch', 'locations', 'currentLocation'));
    }

    /**
     * Show the form for creating a new CCTV.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $cameraBrands = $this->getCameraBrands();
        return view('cctvs.create', compact('cameraBrands'));
    }

    /**
     * Store a newly created CCTV in the database and start streaming.
     * This method saves the CCTV immediately and starts streaming in the background.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'location' => 'required|in:Kilang,Bumi Patra',
            'ip_address' => 'required|ip|unique:cctvs',
            'status' => 'required|in:online,offline',
            'camera_brand' => 'required|string',
            'rtsp_port' => 'required|integer|min:1|max:65535',
            'rtsp_path' => 'required|string',
            'rtsp_username' => 'nullable|string|max:255',
            'rtsp_password' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        // Build RTSP URL with proper encoding
        $rtspUrl = "rtsp://";
        $username = $request->input('rtsp_username');
        $password = $request->input('rtsp_password');
        if ($username && $password) {
            $rtspUrl .= rawurlencode($username) . ":" . rawurlencode($password) . "@";
        }
        $rtspUrl .= $request->input('ip_address') . ":" . $request->input('rtsp_port') . $request->input('rtsp_path');

        // if camera_brand was not supplied or is generic, try detecting via ffprobe
        $cameraBrand = $request->input('camera_brand');
        if (empty($cameraBrand) || $cameraBrand === 'generic') {
            $detected = $this->streamService->detectCameraBrand($rtspUrl);
            if ($detected) {
                $cameraBrand = $detected;
            }
        }

        try {
            $validated['stream_url'] = $rtspUrl;
            $validated['is_streaming'] = false;
            $validated['playlist_url'] = null;

            // Create and save the CCTV immediately
            $cctv = new Cctv($validated);
            if (!empty($cameraBrand)) {
                $cctv->camera_brand = $cameraBrand;
            }
            $cctv->save();

            // Jangan start FFmpeg saat request create agar tidak timeout / server error.
            // start_stream.bat akan tetap dibuat saat halaman detail CCTV di-load (sesuai existing flow).

            return redirect()->route('cctvs.index')
                ->with('success', 'CCTV berhasil ditambahkan! Streaming akan dimulai saat halaman detail video dibuka.');




        } catch (\Exception $e) {
            Log::error('Error creating CCTV: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menambahkan CCTV: ' . $e->getMessage())
                ->withInput();
        }
    }


    /**
     * Display the specified CCTV.
     *
     * @param Cctv $cctv
     * @return \Illuminate\View\View
     */
    public function show(Cctv $cctv)
    {
        $cctv->updateLastSeen();

        $cctvStatistics = [
            'uptime_percentage' => '99.9%',
            'disruption_count' => 5,
            'average_latency' => '45ms',
        ];

        return view('cctvs.show', compact('cctv', 'cctvStatistics'));
    }

    /**
     * Show the form for editing the specified CCTV.
     *
     * @param Cctv $cctv
     * @return \Illuminate\View\View
     */
    public function edit(Cctv $cctv)
    {
        $cameraBrands = $this->getCameraBrands();
        return view('cctvs.edit', compact('cctv', 'cameraBrands'));
    }

    /**
     * Show the confirmation page for deleting a CCTV.
     *
     * @param Cctv $cctv
     * @return \Illuminate\View\View
     */
    public function delete(Cctv $cctv)
    {
        return view('cctvs.delete', compact('cctv'));
    }

    

    /**
     * Update the specified CCTV in the database.
     * This method is now atomic, performing only a single save operation.
     *
     * @param Request $request
     * @param Cctv $cctv
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Cctv $cctv)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'location' => 'required|in:Kilang,Bumi Patra',
        'ip_address' => ['required', 'ip', Rule::unique('cctvs')->ignore($cctv->id)],
        'status' => 'required|in:online,offline',
        'camera_brand' => 'required|string',
        'rtsp_port' => 'required|integer|min:1|max:65535',
        'rtsp_path' => 'required|string',
        'rtsp_username' => 'nullable|string|max:255',
        'rtsp_password' => 'nullable|string|max:255',
        'latitude' => 'nullable|numeric|between:-90,90',
        'longitude' => 'nullable|numeric|between:-180,180',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    $validated = $validator->validated();

    // Stop the existing stream before making any changes
    if ($cctv->is_streaming) {
        $this->streamService->stopStream($cctv);
    }

    // Remove any existing stream files so update can overwrite the previous conversion output
    $oldStreamFolder = $cctv->slug;
    $outputDir = public_path("stream/{$oldStreamFolder}");
    if (File::exists($outputDir)) {
        File::deleteDirectory($outputDir);
    }

    // Prepare the data and fill the model
    $rtspUrl = "rtsp://";
    $username = $request->input('rtsp_username');
    $password = $request->input('rtsp_password');
    if ($username && $password) {
        $rtspUrl .= rawurlencode($username) . ":" . rawurlencode($password) . "@";
    }
    $rtspUrl .= $request->input('ip_address') . ":" . $request->input('rtsp_port') . $request->input('rtsp_path');

    $validated['stream_url'] = $rtspUrl;
    $validated['is_streaming'] = false;
    $validated['playlist_url'] = null;

    if (empty($password)) {
        unset($validated['rtsp_password']);
    }

    // Fill and save the model with the basic updated data first
    $cctv->fill($validated);
    $cctv->save();

        try {
            // Konversi background dihilangkan. 
            // Kita pass `auto_start_cctv_id` ke frontend agar dipanggil via AJAX setelah redirect ke index.
            
            return redirect()->route('cctvs.index')
                ->with('success', 'CCTV berhasil diupdate. Streaming akan dimulai saat halaman detail video dibuka.');


        } catch (\Exception $e) {
            Log::error('Error dispatching background conversion for CCTV ID ' . $cctv->id . ': ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal mengupdate CCTV: ' . $e->getMessage())
                ->withInput();
        }

}


    /**
     * Delete the specified CCTV from the database.
     *
     * @param Cctv $cctv
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Cctv $cctv)
    {
        try {
            // Stop streaming if it is running
            if ($cctv->is_streaming) {
                $this->streamService->stopStream($cctv);
            }

            // Delete the stream folder if it exists
            $streamPath = public_path("stream/{$cctv->slug}");
            if (is_dir($streamPath)) {
                File::deleteDirectory($streamPath);
            }

            // Also delete legacy/alternate output location used by /storage/streams route
            $storageStreamsPath = storage_path("app/public/streams/{$cctv->slug}");
            if (is_dir($storageStreamsPath)) {
                File::deleteDirectory($storageStreamsPath);
            }


            // Delete the CCTV record
            $cctv->delete();

            return redirect()->route('cctvs.index')
                ->with('success', 'CCTV berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus CCTV: ' . $e->getMessage());
        }
    }

    /**
     * Test the connection for a specified CCTV.
     *
     * @param Cctv $cctv
     * @return \Illuminate\Http\JsonResponse
     */
    public function testRtspConnection(Cctv $cctv)
    {
        try {
            // In a real application, this would call a service to test the RTSP connection.
            $isConnected = $this->streamService->testConnection($cctv);

            if ($isConnected) {
                $cctv->updateLastSeen();
                return response()->json(['success' => true, 'message' => 'Connection successful', 'last_seen' => $cctv->last_seen_formatted]);
            } else {
                return response()->json(['success' => false, 'message' => 'Connection failed']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Connection test error: ' . $e->getMessage()]);
        }
    }

    /**
     * Start the streaming process for a specific CCTV.
     *
     * @param Cctv $cctv
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function startStreaming(Cctv $cctv)
    {
        $result = $this->streamService->startStream($cctv->id);

        if (request()->ajax()) {
            if ($result) {
                return response()->json(['success' => true, 'message' => 'Streaming berhasil dimulai!']);
            } else {
                return response()->json(['success' => false, 'message' => 'Gagal memulai streaming.'], 500);
            }
        }

        if ($result) {
            return redirect()->back()->with('success', 'Streaming berhasil dimulai!');
        }

        return redirect()->back()->with('error', 'Gagal memulai streaming.');
    }

    /**
     * Stop the streaming process for a specific CCTV.
     *
     * @param Cctv $cctv
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function stopStreaming(Cctv $cctv)
    {
        $result = $this->streamService->stopStream($cctv);

        if (request()->ajax()) {
            if ($result) {
                return response()->json(['success' => true, 'message' => 'Stream berhasil dihentikan']);
            } else {
                return response()->json(['success' => false, 'message' => 'Gagal menghentikan stream'], 500);
            }
        }

        if ($result) {
            return redirect()->back()->with('success', 'Stream berhasil dihentikan');
        }

        return redirect()->back()->with('error', 'Gagal menghentikan stream');
    }

    /**
     * Toggle the status of a specific CCTV between 'online' and 'offline'.
     *
     * @param Cctv $cctv
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus(Cctv $cctv)
    {
        try {
            $newStatus = $cctv->status === 'online' ? 'offline' : 'online';
            $cctv->update(['status' => $newStatus]);

            return redirect()->back()->with('success', "Status kamera berhasil diubah menjadi {$newStatus}!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah status kamera: ' . $e->getMessage());
        }
    }

    /**
     * Ping a given IP address to check its status.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ping(Request $request)
    {
        $ipAddress = $request->input('ip_address');

        if (!$ipAddress) {
            return response()->json(['status' => 'error', 'message' => 'IP address is required'], 400);
        }

        // Use the 'ping' command to check the host status.
        // The '-n 1' option is for Windows to send only one packet.
        // For Linux/macOS, you would use '-c 1'.
        $command = 'ping -n 1 ' . escapeshellarg($ipAddress);
        exec($command, $output, $status);

        if ($status === 0) {
            return response()->json(['status' => 'online']);
        } else {
            return response()->json(['status' => 'offline']);
        }
    }

    /**
     * Remove multiple CCTV records from the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'cctv_ids' => 'required|array',
            'cctv_ids.*' => 'exists:cctvs,id',
        ]);

        try {
            $cctvIds = $request->input('cctv_ids');
            // Chunk supaya tetap bisa stop ffmpeg -> lalu hapus folder (urutannya terjaga),
            // tapi tidak terlalu berat kalau yang dihapus banyak.
            $chunkSize = 10;

            foreach (array_chunk($cctvIds, $chunkSize) as $chunkIds) {
                $cctvs = Cctv::whereIn('id', $chunkIds)->get();

                foreach ($cctvs as $cctv) {
                    if ($cctv->is_streaming) {
                        $this->streamService->stopStream($cctv);
                    }

                    $streamPath = public_path("stream/{$cctv->slug}");
                    if (is_dir($streamPath)) {
                        File::deleteDirectory($streamPath);
                    }
                }

                Cctv::whereIn('id', $chunkIds)->delete();
            }

            return redirect()->route('cctvs.index')
                ->with('success', count($cctvIds) . ' CCTV berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus CCTV: ' . $e->getMessage());
        }
    }
}

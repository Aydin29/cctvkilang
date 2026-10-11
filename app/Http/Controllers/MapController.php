<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MapController extends Controller
{
    public function index()
    {
        $cctvs = Cctv::all();
        
        // Statistics
        $statistics = [
            'total' => Cctv::count(),
            'online' => Cctv::where('status', 'online')->count(),
            'offline' => Cctv::where('status', 'offline')->count(),
        ];
        
        // Update CCTVs with missing coordinates
        $missingCoordinatesCctvs = $cctvs->filter(function($cctv) {
            return !$cctv->latitude || !$cctv->longitude || 
                   $cctv->latitude == 0 || $cctv->longitude == 0;
        });
        
        if ($missingCoordinatesCctvs->count() > 0) {
            foreach ($missingCoordinatesCctvs as $cctv) {
                $cctv->update([
                    'latitude' => -6.3051,  // Default coordinates for Kilang Pertamina RU VI Balongan
                    'longitude' => 108.3328
                ]);
            }
            
            // Refresh the collection after updates
            $cctvs = Cctv::all();
        }
        
        return view('maps.index', compact('cctvs', 'statistics'));
    }
    
    public function showCctv(Cctv $cctv)
    {
        return view('maps.show', compact('cctv'));
    }
    
    public function updateLocation(Request $request, $cctvId)
    {
        try {
            Log::info('Update location request received', [
                'cctv_id' => $cctvId,
                'request_data' => $request->all(),
                'headers' => $request->headers->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Validasi input
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);
            
            if ($validator->fails()) {
                Log::error('Validation failed', [
                    'cctv_id' => $cctvId,
                    'errors' => $validator->errors()->toArray(),
                    'input_data' => $request->all()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Data koordinat tidak valid',
                    'errors' => $validator->errors()
                ], 400);
            }
            
            // Cari CCTV
            $cctv = Cctv::find($cctvId);
            
            if (!$cctv) {
                Log::error('CCTV not found', [
                    'cctv_id' => $cctvId,
                    'available_cctvs' => Cctv::pluck('id')->toArray()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'CCTV tidak ditemukan'
                ], 404);
            }
            
            Log::info('Found CCTV', [
                'cctv_id' => $cctv->id,
                'cctv_name' => $cctv->name,
                'current_latitude' => $cctv->latitude,
                'current_longitude' => $cctv->longitude,
                'new_latitude' => $request->latitude,
                'new_longitude' => $request->longitude
            ]);
            
            // Update lokasi
            $updateData = [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ];
            
            $updateResult = $cctv->update($updateData);
            
            Log::info('Update result', [
                'cctv_id' => $cctvId,
                'result' => $updateResult,
                'updated_rows' => $updateResult ? 1 : 0,
                'new_data' => $updateData
            ]);
            
            if ($updateResult) {
                // Refresh model untuk mendapatkan data terbaru
                $cctv->refresh();
                
                Log::info('CCTV location updated successfully', [
                    'cctv_id' => $cctv->id,
                    'name' => $cctv->name,
                    'new_latitude' => $cctv->latitude,
                    'new_longitude' => $cctv->longitude,
                    'updated_at' => $cctv->updated_at
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Lokasi CCTV berhasil diperbarui',
                    'data' => [
                        'id' => $cctv->id,
                        'name' => $cctv->name,
                        'latitude' => $cctv->latitude,
                        'longitude' => $cctv->longitude,
                        'updated_at' => $cctv->updated_at->toDateTimeString()
                    ]
                ]);
            } else {
                Log::error('Database update failed', [
                    'cctv_id' => $cctvId,
                    'update_data' => $updateData,
                    'database_error' => 'No rows affected'
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui data di database - tidak ada perubahan yang dilakukan'
                ], 500);
            }
            
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database query error', [
                'cctv_id' => $cctvId,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan database: ' . $e->getMessage()
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Update location error', [
                'cctv_id' => $cctvId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getCctvData()
    {
        try {
            $cctvs = Cctv::select('id', 'name', 'location', 'ip_address', 'status', 'latitude', 'longitude')
                         ->get();
            
            return response()->json([
                'success' => true,
                'data' => $cctvs
            ]);
        } catch (\Exception $e) {
            Log::error('Get CCTV data error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data CCTV'
            ], 500);
        }
    }

    public function liveStream(Cctv $cctv)
    {
        // Generate the playlist URL based on the CCTV's name or ID
        $playlistUrl = $cctv->generateStreamUrl();
        
        return view('maps.live-stream', compact('cctv', 'playlistUrl'));
    }
}

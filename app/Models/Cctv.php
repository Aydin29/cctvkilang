<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $status
 * @property string $ip_address
 * @property int|null $rtsp_port
 * @property string|null $rtsp_path
 * @property string|null $rtsp_username
 * @property string|null $rtsp_password
 * @property string|null $rtsp_url
 * @property string|null $stream_url
 * @property string|null $playlist_url
 * @property bool $is_streaming
 * @property \Illuminate\Support\Carbon|null $last_seen
 */
class Cctv extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cctv) {
            $cctv->slug = self::sanitizeStreamName($cctv->name);
        });

        static::updating(function ($cctv) {
            $cctv->slug = self::sanitizeStreamName($cctv->name);
        });
    }

    public static function sanitizeStreamName(string $name): string
    {
        $name = trim($name);

        // Normalize known variants that differ only by an extra suffix.
        // Example on your disk:
        // - "ip 27 (not fixedddd)" playlists should resolve as "ip 27 (not fixed)"
        // This avoids 404 on /stream/{slug}/playlist.m3u8 when frontend slug != folder name.
        $name = str_replace('not fixedddd', 'not fixed', $name);

        // Extra normalization for safety (collapse double spaces, etc.)
        $name = preg_replace('/\s+/', ' ', $name);

        // Ensure slugs remain consistent with output folders
        // (some folders were generated with "not fixed" naming).
        // No other changes.


        $name = preg_replace('/[\/\\:\*\?"<>\|]+/', '_', $name);
        $name = preg_replace('/\s+/', ' ', $name);

        return $name;
    }


    protected $table = 'cctvs';

    protected $fillable = [
        'name',
        'slug',
        'location',
        'ip_address',
        'status',
        'camera_brand',
        'rtsp_port',
        'rtsp_path',
        'rtsp_username',
        'rtsp_password',
        'latitude',
        'longitude',
        'stream_url',
        'playlist_url',
        'is_streaming',
        'last_seen',
        'description',
    ];

    protected $casts = [
        'last_seen' => 'datetime',
        'is_streaming' => 'boolean',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
    ];

    protected $hidden = [
        'rtsp_password',
    ];

    protected $appends = [
        'last_seen_formatted',
        'status_badge',
        'streaming_badge',
        'rtsp_url',
    ];

    /**
     * Encrypt password when saving.
     */
    public function setRtspPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['rtsp_password'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt password when retrieving.
     */
    public function getRtspPasswordAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Mutator untuk RTSP path.
     * Pastikan path selalu diawali dengan slash dan tidak URL encoded.
     */
    public function setRtspPathAttribute($value)
    {
        if ($value) {
            $decoded = urldecode($value);
            if (!str_starts_with($decoded, '/')) {
                $decoded = '/' . $decoded;
            }
            $this->attributes['rtsp_path'] = $decoded;
        } else {
            $this->attributes['rtsp_path'] = null;
        }
    }

    /**
     * Get complete RTSP URL (as attribute).
     */
    public function getRtspUrlAttribute()
    {
        $url = "rtsp://";

        if ($this->rtsp_username && $this->rtsp_password) {
            $url .= $this->rtsp_username . ":" . $this->rtsp_password . "@";
        }

        $url .= $this->ip_address . ":" . $this->rtsp_port;

        if ($this->rtsp_path) {
            $url .= $this->rtsp_path;
        }

        return $url;
    }

    /**
     * Get status badge HTML.
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'online' => '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Online</span>',
            'offline' => '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Offline</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get streaming status badge.
     */
    public function getStreamingBadgeAttribute()
    {
        if ($this->is_streaming) {
            return '<span class="badge bg-success"><i class="fas fa-broadcast-tower me-1"></i>Streaming</span>';
        }
        return '<span class="badge bg-secondary"><i class="fas fa-stop-circle me-1"></i>Not Streaming</span>';
    }

    /**
     * Get formatted last seen.
     */
    public function getLastSeenFormattedAttribute()
    {
        return $this->last_seen ? $this->last_seen->diffForHumans() : 'Never';
    }

    /**
     * Get full RTSP URL (as method).
     */
    public function getRtspFullUrl()
    {
        $auth = ($this->rtsp_username && $this->rtsp_password)
            ? "{$this->rtsp_username}:{$this->rtsp_password}@"
            : '';

        return "rtsp://{$auth}{$this->ip_address}:{$this->rtsp_port}{$this->rtsp_path}";
    }

    /**
     * Generate M3U8 stream URL.
     */
    public function generateStreamUrl()
    {
        return url("/stream/{$this->slug}/playlist.m3u8");
    }

    /**
     * Get the playlist URL attribute.
     */
    public function getPlaylistUrlAttribute($value)
    {
        return $value ?: url("stream/{$this->slug}/playlist.m3u8");
    }

    /**
     * Set the playlist URL attribute.
     */
    public function setPlaylistUrlAttribute($value)
    {
        $this->attributes['playlist_url'] = $value;
    }

    /**
     * Get the stream start URL for API calls.
     */
    public function getStreamStartUrlAttribute()
    {
        return url("stream/{$this->slug}/start");
    }

    /**
     * Check if the stream is currently active and accessible.
     */
    public function isStreamActive()
    {
        if (!$this->is_streaming) {
            return false;
        }

        return !empty($this->playlist_url) && $this->is_streaming;
    }

    /**
     * Update last seen timestamp.
     */
    public function updateLastSeen()
    {
        $this->last_seen = now();
        $this->save();
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    /**
     * Scope for searching.
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    /**
     * Get statistics using a single optimized query.
     */
    public static function getStatistics()
    {
        $stats = self::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = "online" THEN 1 ELSE 0 END) as online,
            SUM(CASE WHEN status = "offline" THEN 1 ELSE 0 END) as offline
        ')->first(['*']);

        return [
            'total' => $stats->total,
            'online' => $stats->online,
            'offline' => $stats->offline,
        ];
    }
}

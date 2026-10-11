<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CctvCamera extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rtsp_url',
        'hls_url',
        'status',
        'server_process_id',
    ];
}


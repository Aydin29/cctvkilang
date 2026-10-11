<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalysisReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'cctv_id',
        'protocol',
        'metrics',
        'chart_data',
    ];

    protected $casts = [
        'metrics' => 'array',
        'chart_data' => 'array',
    ];

    public function cctv()
    {
        return $this->belongsTo(Cctv::class);
    }
}

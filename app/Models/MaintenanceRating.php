<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRating extends Model
{
    use SoftDeletes;

    protected $table = 'maintenance_ratings';

    protected $fillable = [
        'maintenance_request_id',
        'rater_id',
        'technician_id',
        'score',
        'comment',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function request()
    {
        return $this->belongsTo(MaintenanceRequest::class, 'maintenance_request_id');
    }

    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes (ใช้บ่อยใน Report / Analytics)
    |--------------------------------------------------------------------------
    */

    public function scopeForTechnician($query, $technicianId)
    {
        return $query->where('technician_id', $technicianId);
    }

    public function scopeForRequest($query, $requestId)
    {
        return $query->where('maintenance_request_id', $requestId);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods (ใช้เช็คสิทธิ์และตรรกะในระบบ)
    |--------------------------------------------------------------------------
    */

    public static function hasRated($requestId, $raterId): bool
    {
        return self::where('maintenance_request_id', $requestId)
            ->where('rater_id', $raterId)
            ->exists();
    }

    public function technicianDashboard()
    {
        // ดึงช่างทุกคนพร้อม avg + count
        $technicians = User::where('role', 'technician')
            ->withAvg('technicianRatings', 'score')
            ->withCount('technicianRatings')
            ->get();

        // เตรียม data สำหรับกราฟ
        $chartLabels = $technicians->pluck('name');
        $chartAvg    = $technicians->pluck('technician_ratings_avg_score');
        $chartCount  = $technicians->pluck('technician_ratings_count');

        return view('maintenance.rating.technicians-dashboard', [
            'technicians' => $technicians,
            'chartLabels' => $chartLabels,
            'chartAvg'    => $chartAvg,
            'chartCount'  => $chartCount,
        ]);
    }

}

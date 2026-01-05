<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'service_id',
        'date','time',
        'status',
        'medical_record'
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_MISSED = 'missed';
    public const STATUS_RESCHEDULED = 'rescheduled';

    public function patient()
    {
        return $this->belongsTo(User::class,'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class,'doctor_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}

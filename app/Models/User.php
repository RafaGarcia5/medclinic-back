<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'email_verified_at',
    ];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'doctor_service', 'doctor_id', 'service_id');
    }

    public function appointmentsAsPatient()
    {
        return $this->hasMany(Appointment::class,'patient_id');
    }

    public function appointmentsAsDoctor()
    {
        return $this->hasMany(Appointment::class,'doctor_id');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'description'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function doctors()
    {
        return $this->belongsToMany(User::class,'doctor_service','service_id','doctor_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

}

<?php

namespace Database\Factories;

use App\Models\DoctorService;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorServiceFactory extends Factory
{
    protected $model = DoctorService::class;

    public function definition(): array
    {
        return [
            'doctor_id' => $this->faker->randomDigit(),
            'service_id' => $this->faker->numberBetween(1,15),
        ];
    }
}

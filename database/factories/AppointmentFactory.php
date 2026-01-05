<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $doctor = User::factory()->doctor()->create();
        $patient = User::factory()->patient()->create();
        $service = Service::factory()->create();

        return [
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'service_id' => $service->id,
            'date' => fake()->dateTimeBetween('now', '+1 week')->format('Y-m-d'),
            'time' => fake()->randomElement(['09:00', '10:00', '11:00', '13:00', '14:00', '15:00']),
            'medical_record' => fake()->optional()->sentence(),
            'status' => Appointment::STATUS_ACTIVE,
        ];
    }
}

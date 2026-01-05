<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Service;
use App\Models\DoctorService;
use App\Models\Appointment;

class DoctorControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_doctor_availability() {
        $doctor = User::factory()->doctor()->create();
        Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'date' => now()->toDateString(),
            'time' => '09:00',
        ]);

        $response = $this->actingAs($doctor)->getJson("/api/doctors/{$doctor->id}/availability?date=" . now()->toDateString());

        $response->assertStatus(200)
                 ->assertJsonStructure(['available', 'unavailable']);
    }

    public function test_get_services_by_doctor() {
        $doctor = User::factory()->doctor()->create();
        
        $services = Service::factory()->count(3)->create();

        foreach ($services as $service) {
            DoctorService::factory()->create([
                'doctor_id' => $doctor->id,
                'service_id' => $service->id,
            ]);
        }

        $response = $this->actingAs($doctor)->getJson('/api/my-services');

        $response->assertStatus(200)
                 ->assertJsonFragment([ 'doctor_id' => $doctor->id ]);
    }

    public function test_doctor_can_attach_service() {
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create();

        $response = $this->actingAs($doctor)
                         ->postJson('/api/doctor-services', ['service_id' => $service->id]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $service->id]);
    }

    public function test_doctor_can_unattach_service() {
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create();
        DoctorService::factory()->create([
            'doctor_id' => $doctor->id,
            'service_id' => $service->id
        ]);

        $response = $this->actingAs($doctor)
                         ->postJson('/api/unlink-doctor-service', ['service_id' => $service->id]);

        $response->assertStatus(200);
    }
}

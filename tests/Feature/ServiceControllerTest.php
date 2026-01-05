<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Service;
use App\Models\User;
use App\Models\DoctorService;

class ServiceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_service() {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
                         ->postJson('/api/services', [
                             'name' => 'Test Service',
                             'description' => 'Test description',
                         ]);

        $response->assertStatus(201)
                 ->assertJson(['name' => 'Test Service']);
    }

    public function test_non_admin_can_list_services() {
        $patient = User::factory()->patient()->create();
        Service::factory()->count(2)->create();

        $response = $this->actingAs($patient)
                         ->getJson('/api/services');

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }

    public function test_get_services_with_doctors() {
        $doctor = User::factory()->doctor()->create();
        $services = Service::factory()->count(3)->create();

        foreach ($services as $service) {
            DoctorService::factory()->create([
                'doctor_id' => $doctor->id,
                'service_id' => $service->id,
            ]);
        }

        $response = $this->actingAs($doctor)->getJson("/api/services/{$services[0]->id}/doctors");

        $response->assertStatus(200)
                 ->assertJsonFragment([ 'id' => $doctor->id, 'name' => $doctor->name ]);
    }

    public function test_update_service() {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create([
            'name' => 'Test case',
            'description' => 'Description test'
        ]);

        $response = $this->actingAs($admin)
                    ->putJson("/api/services/{$service->id}", [
                        'description' => 'Modified description',
                    ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Service updated successfully']);        
    }
    
    public function test_delete_service() {
        $admin = User::factory()->admin()->create();
        $service = Service::factory()->create([
            'name' => 'Test case',
            'description' => 'Description test'
        ]);

        $response = $this->actingAs($admin)
                    ->deleteJson("/api/services/{$service->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Service successfully removed']);        
    }
}

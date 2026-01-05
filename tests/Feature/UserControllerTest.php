<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_doctors() {
        $admin = User::factory()->admin()->create();
        $doctor = User::factory()->doctor()->create();

        $response = $this->actingAs($admin)
                         ->getJson('/api/admin/doctors');

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $doctor->id]);
    }

    public function test_admin_cannot_delete_other_admin() {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
                         ->deleteJson("/api/admin/users/{$otherAdmin->id}");

        $response->assertStatus(403)
                 ->assertJson(['error' => 'An administrator cannot be removed']);
    }

    public function test_admin_can_delete_patient() {
        $admin = User::factory()->admin()->create();
        $patient = User::factory()->patient()->create();

        $response = $this->actingAs($admin)
                         ->deleteJson("/api/admin/users/{$patient->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'User successfully deleted']);
    }
}

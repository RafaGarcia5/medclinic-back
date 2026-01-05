<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Service;
use App\Models\Appointment;

class AppointmentControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_admin_can_get_paginated_appointments() {
        $admin = User::factory()->admin()->create();
        Appointment::factory()->count(5)->create();

        $response = $this->actingAs($admin)->getJson('/api/appointments?per_page=2');
        $response->assertOk()
                 ->assertJsonStructure(['data', 'links', 'last_page']);
    }

    public function test_doctor_can_view_their_appointments() {
        $doctor = User::factory()->doctor()->create();
        $appointment = Appointment::factory()->create(['doctor_id' => $doctor->id]);

        $response = $this->actingAs($doctor)->getJson('/api/appointments');
        $response->assertOk()
                 ->assertJsonFragment(['id' => $appointment->id]);
    }

    public function test_patient_can_view_their_appointments() {
        $patient = User::factory()->create(['role' => 'patient']);
        $appointment = Appointment::factory()->create(['patient_id' => $patient->id]);

        $response = $this->actingAs($patient)->getJson('/api/appointments');
        $response->assertOk()
                 ->assertJsonFragment(['id' => $appointment->id]);
    }

    public function test_patient_can_create_appointment() {
        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create();

        $response = $this->actingAs($patient)
                         ->postJson('/api/appointments', [
                             'doctor_id' => $doctor->id,
                             'service_id' => $service->id,
                             'date' => now()->addDay()->toDateString(),
                             'time' => '09:00',
                         ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'doctor_id', 'patient_id', 'service_id']);
    }

    public function test_patient_can_reschedule_appointment() {
        $patient = User::factory()->patient()->create();
        $appointment = Appointment::factory()->create(['patient_id' => $patient->id]);

        $token = $patient->createToken('auth')->plainTextToken;

        $response = $this->actingAs($patient)
                         ->postJson("/api/appointments/{$appointment->id}/reschedule", [
                             'date' => now()->addDays(2)->toDateString(),
                             'time' => '11:00',
                         ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => 'rescheduled']);
    }

    public function test_can_cancel_an_appointment() {
        $admin = User::factory()->admin()->create();
        $appointment = Appointment::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/appointments/{$appointment->id}");
        $response->assertOk()
                 ->assertJsonFragment(['message' => 'Appointment cancelled']);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::STATUS_CANCELLED,
        ]);
    }

    public function test_patient_can_reschedule_appointment_if_slot_free() {
        $patient = User::factory()->patient()->create();
        $appointment = Appointment::factory()->create(['patient_id' => $patient->id]);

        $payload = [
            'date' => now()->addDays(3)->format('Y-m-d'),
            'time' => '14:00'
        ];

        $response = $this->actingAs($patient)->postJson("/api/appointments/{$appointment->id}/reschedule", $payload);

        $response->assertOk()
                 ->assertJsonFragment([
                     'date' => $payload['date'],
                     'time' => $payload['time'],
                     'status' => 'rescheduled',
                 ]);
    }

    public function test_reschedule_fails_if_slot_taken() {
        $doctor = User::factory()->doctor()->create();
        $patient = User::factory()->patient()->create();

        $existing = Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'date' => now()->addDays(1)->format('Y-m-d'),
            'time' => '10:00',
            'status' => 'active',
        ]);

        $appointment = Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'time' => '11:00',
        ]);

        $payload = [
            'date' => $existing->date,
            'time' => $existing->time,
        ];

        $response = $this->actingAs($patient)->postJson("/api/appointments/{$appointment->id}/reschedule", $payload);

        $response->assertStatus(422)
                 ->assertJsonFragment(['error' => 'El doctor ya tiene una cita en ese horario']);
    }

}

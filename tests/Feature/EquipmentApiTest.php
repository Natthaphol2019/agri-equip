<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Equipment;

class EquipmentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_equipments_returns_list()
    {
        // Arrange: create a couple of equipments
        Equipment::create([
            'equipment_code' => 'EQ-001',
            'name' => 'Drone A',
            'type' => 'drone',
            'maintenance_hour_threshold' => 100,
            'current_status' => 'available'
        ]);

        // Act
        $response = $this->getJson('/api/equipments');

        // Assert
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     [
                         'id','equipment_code','name','type','maintenance_hour_threshold','current_status'
                     ]
                 ]);
    }

    public function test_store_equipment_with_image_creates_and_returns_201()
    {
        Storage::fake('public');

        $payload = [
            'name' => 'Tractor X',
            'type' => 'tractor',
            'maintenance_hour_threshold' => 250,
            'registration_number' => 'REG-1234',
            'image' => UploadedFile::fake()->image('tractor.jpg')
        ];

        $response = $this->postJson('/api/equipments', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'data' => ['id','equipment_code','name','type','image_path']
                 ]);

        // Check file stored
        $data = $response->json('data');
        $this->assertNotNull($data['image_path']);
        Storage::disk('public')->assertExists($data['image_path']);

        // Check DB
        $this->assertDatabaseHas('equipment', [
            'name' => 'Tractor X',
            'registration_number' => 'REG-1234'
        ]);
    }
}

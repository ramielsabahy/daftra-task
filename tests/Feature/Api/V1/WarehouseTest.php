<?php

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('authenticated user can list warehouses', function () {
    Warehouse::factory()->count(3)->create();

    $response = $this->actingAs($this->user)->getJson(route('warehouses.index'));

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('authenticated user can create a warehouse', function () {
    $data = [
        'name' => 'New Warehouse',
        'code' => 'WH-NEW',
        'location' => 'Cairo',
        'is_active' => true,
    ];

    $response = $this->actingAs($this->user)->postJson(route('warehouses.store'), $data);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'New Warehouse');

    $this->assertDatabaseHas('warehouses', ['code' => 'WH-NEW']);
});

test('authenticated user can view a warehouse', function () {
    $warehouse = Warehouse::factory()->create();

    $response = $this->actingAs($this->user)->getJson(route('warehouses.show', $warehouse));

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $warehouse->id);
});

test('authenticated user can update a warehouse', function () {
    $warehouse = Warehouse::factory()->create();
    $data = ['name' => 'Updated Name'];

    $response = $this->actingAs($this->user)->putJson(route('warehouses.update', $warehouse), $data);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Updated Name');

    $this->assertDatabaseHas('warehouses', [
        'id' => $warehouse->id,
        'name' => 'Updated Name',
    ]);
});

test('authenticated user can delete a warehouse', function () {
    $warehouse = Warehouse::factory()->create();

    $response = $this->actingAs($this->user)->deleteJson(route('warehouses.destroy', $warehouse));

    $response->assertStatus(200);

    $this->assertSoftDeleted('warehouses', ['id' => $warehouse->id]);
});

test('unauthenticated user cannot access warehouse endpoints', function () {
    $this->getJson(route('warehouses.index'))->assertStatus(401);
    $this->postJson(route('warehouses.store'), [])->assertStatus(401);
});

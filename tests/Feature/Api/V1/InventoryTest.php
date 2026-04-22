<?php

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('authenticated user can list inventory', function () {
    Inventory::factory()->count(3)->create();

    $response = $this->actingAs($this->user)->getJson(route('inventory.index'));

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('authenticated user can view specific inventory by id', function () {
    $inventory = Inventory::factory()->create();

    $response = $this->actingAs($this->user)->getJson(route('inventory.show', $inventory->id));

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $inventory->id);
});

test('inventory list can be filtered by warehouse_id', function () {
    $inventory1 = Inventory::factory()->create();
    $inventory2 = Inventory::factory()->create();

    $response = $this->actingAs($this->user)->getJson(route('inventory.index', [
        'warehouse_id' => $inventory1->warehouse_id
    ]));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $inventory1->id);
});

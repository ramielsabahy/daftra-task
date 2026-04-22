<?php

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('authenticated user can list items', function () {
    Item::factory()->count(5)->create();

    $response = $this->actingAs($this->user)->getJson(route('items.index'));

    $response->assertStatus(200)
        ->assertJsonCount(5, 'data');
});

test('authenticated user can create an item', function () {
    $data = [
        'sku' => 'ITEM-001',
        'name' => 'New Item',
        'description' => 'Item Description',
        'unit' => 'pcs',
        'low_stock_threshold' => 10,
    ];

    $response = $this->actingAs($this->user)->postJson(route('items.store'), $data);

    $response->assertStatus(201)
        ->assertJsonPath('data.sku', 'ITEM-001');

    $this->assertDatabaseHas('items', ['sku' => 'ITEM-001']);
});

test('authenticated user can update an item', function () {
    $item = Item::factory()->create();
    $data = ['name' => 'Updated Item Name'];

    $response = $this->actingAs($this->user)->putJson(route('items.update', $item), $data);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Updated Item Name');

    $this->assertDatabaseHas('items', [
        'id' => $item->id,
        'name' => 'Updated Item Name',
    ]);
});

test('authenticated user can delete an item', function () {
    $item = Item::factory()->create();

    $response = $this->actingAs($this->user)->deleteJson(route('items.destroy', $item));

    $response->assertStatus(200);

    $this->assertSoftDeleted('items', ['id' => $item->id]);
});

<?php

use App\Models\Inventory;
use App\Models\Item;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('authenticated user can list transfers', function () {
    StockTransfer::factory()->count(3)->create();

    $response = $this->actingAs($this->user)->getJson(route('transfers.index'));

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('user can perform a valid stock transfer', function () {
    $item = Item::factory()->create();
    $fromWH = Warehouse::factory()->create(['is_active' => true]);
    $toWH = Warehouse::factory()->create(['is_active' => true]);

    // Initial stock
    Inventory::factory()->create([
        'item_id' => $item->id,
        'warehouse_id' => $fromWH->id,
        'quantity' => 100
    ]);

    $data = [
        'from_warehouse_id' => $fromWH->id,
        'to_warehouse_id' => $toWH->id,
        'item_id' => $item->id,
        'quantity' => 40,
        'notes' => 'Test transfer'
    ];

    $response = $this->actingAs($this->user)->postJson(route('transfers.store'), $data);

    $response->assertStatus(201)
        ->assertJsonPath('data.status', StockTransfer::STATUS_COMPLETED);

    $this->assertDatabaseHas('inventories', [
        'item_id' => $item->id,
        'warehouse_id' => $fromWH->id,
        'quantity' => 60
    ]);

    $this->assertDatabaseHas('inventories', [
        'item_id' => $item->id,
        'warehouse_id' => $toWH->id,
        'quantity' => 40
    ]);
});

test('transfer fails if insufficient stock', function () {
    $item = Item::factory()->create();
    $fromWH = Warehouse::factory()->create();
    $toWH = Warehouse::factory()->create();

    Inventory::factory()->create([
        'item_id' => $item->id,
        'warehouse_id' => $fromWH->id,
        'quantity' => 10
    ]);

    $data = [
        'from_warehouse_id' => $fromWH->id,
        'to_warehouse_id' => $toWH->id,
        'item_id' => $item->id,
        'quantity' => 20,
    ];

    $response = $this->actingAs($this->user)->postJson(route('transfers.store'), $data);

    $response->assertStatus(422)
        ->assertJsonPath('success', false);
});

test('transfer fails if source and destination warehouses are the same', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $data = [
        'from_warehouse_id' => $warehouse->id,
        'to_warehouse_id' => $warehouse->id,
        'item_id' => $item->id,
        'quantity' => 5,
    ];

    $response = $this->actingAs($this->user)->postJson(route('transfers.store'), $data);

    $response->assertStatus(422)
        ->assertJsonPath('success', false);
});

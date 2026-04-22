<?php

use App\Models\Inventory;
use App\Models\Item;
use App\Models\LowStockEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('authenticated user can list low stock alerts', function () {
    // Item with low stock threshold 10
    $item = Item::factory()->create(['low_stock_threshold' => 10]);

    // Inventory below threshold
    $inventory = Inventory::factory()->create([
        'item_id' => $item->id,
        'quantity' => 5
    ]);

    // Create the event
    LowStockEvent::create([
        'inventory_id' => $inventory->id,
        'triggered_at' => now(),
    ]);

    // Inventory above threshold
    Inventory::factory()->create([
        'item_id' => Item::factory()->create(['low_stock_threshold' => 10]),
        'quantity' => 20
    ]);

    $response = $this->actingAs($this->user)->getJson(route('alerts.low-stock'));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

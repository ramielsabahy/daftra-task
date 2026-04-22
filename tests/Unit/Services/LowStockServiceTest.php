<?php

use App\Models\Inventory;
use App\Models\Item;
use App\Models\LowStockEvent;
use App\Services\LowStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new LowStockService();
});

test('evaluate creates a low stock event if stock is below threshold', function () {
    $item = Item::factory()->create(['low_stock_threshold' => 10]);
    $inventory = Inventory::factory()->create([
        'item_id' => $item->id,
        'quantity' => 5
    ]);

    $this->service->evaluate($inventory);

    $this->assertDatabaseHas('low_stock_events', [
        'inventory_id' => $inventory->id,
        'resolved_at' => null
    ]);
});

test('evaluate does not create duplicate events', function () {
    $item = Item::factory()->create(['low_stock_threshold' => 10]);
    $inventory = Inventory::factory()->create([
        'item_id' => $item->id,
        'quantity' => 5
    ]);

    // Create an existing open event
    LowStockEvent::create([
        'inventory_id' => $inventory->id,
        'triggered_at' => now(),
    ]);

    $this->service->evaluate($inventory);

    expect(LowStockEvent::where('inventory_id', $inventory->id)->count())->toBe(1);
});

test('evaluate resolves event if stock is replenished', function () {
    $item = Item::factory()->create(['low_stock_threshold' => 10]);
    $inventory = Inventory::factory()->create([
        'item_id' => $item->id,
        'quantity' => 20
    ]);

    // Create an existing open event (simulating it was low before)
    $event = LowStockEvent::create([
        'inventory_id' => $inventory->id,
        'triggered_at' => now()->subDay(),
    ]);

    $this->service->evaluate($inventory);

    $event->refresh();
    expect($event->resolved_at)->not->toBeNull();
});

test('evaluate ignores if threshold is not set or zero', function () {
    $item = Item::factory()->create(['low_stock_threshold' => 0]);
    $inventory = Inventory::factory()->create([
        'item_id' => $item->id,
        'quantity' => -1
    ]);

    $this->service->evaluate($inventory);

    $this->assertDatabaseEmpty('low_stock_events');
});

<?php

namespace Tests\Unit\Services;

use App\Exceptions\InactiveWarehouseException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\SameWarehouseTransferException;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\StockTransferService;
use App\Repositories\InventoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->inventoryRepository = new InventoryRepository();
    $this->inventoryService = new InventoryService($this->inventoryRepository);
    $this->service = new StockTransferService($this->inventoryService);
    $this->user = User::factory()->create();
});

test('transfer stock successfully', function () {
    $item = Item::factory()->create();
    $fromWH = Warehouse::factory()->create(['is_active' => true]);
    $toWH = Warehouse::factory()->create(['is_active' => true]);

    $sourceInventory = Inventory::factory()->create([
        'item_id' => $item->id,
        'warehouse_id' => $fromWH->id,
        'quantity' => 100
    ]);

    $data = [
        'from_warehouse_id' => $fromWH->id,
        'to_warehouse_id' => $toWH->id,
        'item_id' => $item->id,
        'quantity' => 30,
        'notes' => 'Unit test transfer'
    ];

    $transfer = $this->service->transfer($data, $this->user);

    expect($transfer->quantity)->toBe(30);
    
    $sourceInventory->refresh();
    expect($sourceInventory->quantity)->toBe(70);

    $destInventory = Inventory::where('item_id', $item->id)
        ->where('warehouse_id', $toWH->id)
        ->first();
    
    expect($destInventory->quantity)->toBe(30);
});

test('transfer throws exception if same warehouse', function () {
    $warehouse = Warehouse::factory()->create(['is_active' => true]);
    
    $data = [
        'from_warehouse_id' => $warehouse->id,
        'to_warehouse_id' => $warehouse->id,
        'item_id' => 1,
        'quantity' => 10,
    ];

    $this->service->transfer($data, $this->user);
})->throws(SameWarehouseTransferException::class);

test('transfer throws exception if warehouse is inactive', function () {
    $item = Item::factory()->create();
    $fromWH = Warehouse::factory()->create(['is_active' => false]);
    $toWH = Warehouse::factory()->create(['is_active' => true]);

    $data = [
        'from_warehouse_id' => $fromWH->id,
        'to_warehouse_id' => $toWH->id,
        'item_id' => $item->id,
        'quantity' => 10,
    ];

    $this->service->transfer($data, $this->user);
})->throws(InactiveWarehouseException::class);

test('transfer throws exception if insufficient stock', function () {
    $item = Item::factory()->create();
    $fromWH = Warehouse::factory()->create(['is_active' => true]);
    $toWH = Warehouse::factory()->create(['is_active' => true]);

    Inventory::factory()->create([
        'item_id' => $item->id,
        'warehouse_id' => $fromWH->id,
        'quantity' => 5
    ]);

    $data = [
        'from_warehouse_id' => $fromWH->id,
        'to_warehouse_id' => $toWH->id,
        'item_id' => $item->id,
        'quantity' => 10,
    ];

    $this->service->transfer($data, $this->user);
})->throws(InsufficientStockException::class);

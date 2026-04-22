<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin user ────────────────────────────────────────────────────
        $user = User::firstOrCreate(
            ['email' => 'admin@inventory.test'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        // ── Warehouses ────────────────────────────────────────────────────
        $warehouseA = Warehouse::firstOrCreate(
            ['code' => 'WH-A'],
            ['name' => 'Main Warehouse', 'location' => 'Cairo', 'is_active' => true]
        );

        $warehouseB = Warehouse::firstOrCreate(
            ['code' => 'WH-B'],
            ['name' => 'Secondary Warehouse', 'location' => 'Alexandria', 'is_active' => true]
        );

        // ── Items ─────────────────────────────────────────────────────────
        $laptop = Item::firstOrCreate(
            ['sku' => 'LAPTOP-001'],
            [
                'name'                => 'Laptop Pro 15"',
                'description'         => 'High-performance laptop',
                'unit'                => 'pcs',
                'low_stock_threshold' => 5,
            ]
        );

        $keyboard = Item::firstOrCreate(
            ['sku' => 'KBD-002'],
            [
                'name'                => 'Mechanical Keyboard',
                'description'         => 'Tenkeyless mechanical keyboard',
                'unit'                => 'pcs',
                'low_stock_threshold' => 10,
            ]
        );

        // ── Inventory ─────────────────────────────────────────────────────
        Inventory::updateOrCreate(
            ['item_id' => $laptop->id,   'warehouse_id' => $warehouseA->id],
            ['quantity' => 50]
        );

        Inventory::updateOrCreate(
            ['item_id' => $keyboard->id, 'warehouse_id' => $warehouseA->id],
            ['quantity' => 200]
        );

        Inventory::updateOrCreate(
            ['item_id' => $laptop->id,   'warehouse_id' => $warehouseB->id],
            ['quantity' => 10]
        );

        Inventory::updateOrCreate(
            ['item_id' => $keyboard->id, 'warehouse_id' => $warehouseB->id],
            ['quantity' => 8]   // deliberately below threshold to trigger low stock
        );

        $this->command->info('✅ Seeded: 1 admin user, 2 warehouses, 2 items, 4 inventory lines.');
        $this->command->info('   Login → POST /api/v1/auth/login  |  email: admin@inventory.test  |  password: password');
    }
}

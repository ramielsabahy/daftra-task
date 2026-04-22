<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockTransfer>
 */
class StockTransferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'from_warehouse_id' => Warehouse::factory(),
            'to_warehouse_id' => Warehouse::factory(),
            'item_id' => Item::factory(),
            'transferred_by' => User::factory(),
            'quantity' => $this->faker->numberBetween(1, 50),
            'status' => StockTransfer::STATUS_COMPLETED,
            'notes' => $this->faker->sentence(),
        ];
    }
}

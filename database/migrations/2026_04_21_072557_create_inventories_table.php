<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();

            // Each item can only have one inventory row per warehouse
            $table->unique(['item_id', 'warehouse_id']);
        });

        // DB-level guard against negative quantity (MySQL 8+ / MariaDB support)
        DB::statement('ALTER TABLE inventories ADD CONSTRAINT chk_quantity_non_negative CHECK (quantity >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('pizza_id')->nullable()->constrained()->onDelete('set null');
            $table->string('pizza_name');
            $table->integer('quantity')->default(1);
            $table->enum('size', ['small', 'medium', 'large'])->default('medium');
            $table->enum('crust', ['thin', 'regular', 'thick', 'stuffed'])->default('regular');
            $table->boolean('is_custom')->default(false);
            $table->decimal('unit_price', 8, 2);
            $table->decimal('toppings_price', 8, 2)->default(0);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });

        Schema::create('order_item_toppings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('topping_id')->nullable()->constrained()->onDelete('set null');
            $table->string('topping_name');
            $table->decimal('topping_price', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_toppings');
        Schema::dropIfExists('order_items');
    }
};

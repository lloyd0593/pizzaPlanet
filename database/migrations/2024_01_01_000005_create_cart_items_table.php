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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('pizza_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->enum('size', ['small', 'medium', 'large'])->default('medium');
            $table->enum('crust', ['thin', 'regular', 'thick', 'stuffed'])->default('regular');
            $table->boolean('is_custom')->default(false);
            $table->string('custom_name')->nullable();
            $table->decimal('unit_price', 8, 2);
            $table->timestamps();
        });

        Schema::create('cart_item_toppings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('topping_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['cart_item_id', 'topping_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_item_toppings');
        Schema::dropIfExists('cart_items');
    }
};

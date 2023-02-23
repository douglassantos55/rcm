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
        Schema::create('equipment', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('description')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('profit_percentage')->nullable();
            $table->decimal('weight', 10)->nullable();
            $table->unsignedInteger('in_stock')->nullable();
            $table->unsignedInteger('effective_qty')->nullable();
            $table->unsignedInteger('min_qty')->nullable();
            $table->decimal('purchase_value', 10)->nullable();
            $table->decimal('unit_value', 10)->nullable();
            $table->decimal('replace_value', 10)->nullable();
            $table->foreignUuid('supplier_id')->nullable()->references('id')->on('suppliers');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};

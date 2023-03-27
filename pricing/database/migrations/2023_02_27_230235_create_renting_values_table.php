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
        Schema::create('renting_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_id');
            $table->foreignUuid('period_id')->references('id')->on('periods')->cascadeOnDelete();
            $table->decimal('value');
            $table->timestamps();
            $table->unique(['equipment_id', 'period_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('renting_values');
    }
};

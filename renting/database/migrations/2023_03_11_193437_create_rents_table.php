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
        Schema::create('rents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->uuid('payment_type_id');
            $table->uuid('payment_method_id');
            $table->uuid('payment_condition_id');
            $table->foreignUuid('customer_id')->references('id')->on('customers');
            $table->foreignUuid('period_id')->references('id')->on('periods');
            $table->integer('qty_days')->unsigned();
            $table->decimal('discount', 10)->nullable();
            $table->decimal('paid_value', 10)->nullable();
            $table->decimal('bill', 10)->nullable();
            $table->decimal('delivery_value', 10)->nullable();
            $table->string('check_info')->nullable();
            $table->string('delivery_address')->nullable();
            $table->string('discount_reason')->nullable();
            $table->string('usage_address')->nullable();
            $table->text('observations')->nullable();
            $table->string('transporter')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rents');
    }
};

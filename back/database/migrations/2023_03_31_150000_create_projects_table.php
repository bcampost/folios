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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->bigInteger('owner_id')->index();
            $table->bigInteger('customer_id')->index()->nullable();
            $table->unsignedBigInteger('deal_id')->index()->nullable();
            $table->bigInteger('branch_id')->index()->nullable();
            $table->bigInteger('payment_term_id')->index()->nullable();
            $table->string('value');
            $table->string('channel');
            $table->string('discount')->nullable();
            $table->string('modality')->nullable();
            $table->string('negotiated_days')->nullable();
            $table->boolean('payment_by_customer_platform')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

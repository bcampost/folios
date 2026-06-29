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
        Schema::create('folio_transitions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable()->index();
            $table->foreignId('folio_id')->constrained();
            $table->foreignId('prev_state_id')->constrained('folio_states');
            $table->foreignId('next_state_id')->constrained('folio_states');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folio_state_transitions');
    }
};

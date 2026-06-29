<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('folio_code_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('classification');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['type', 'classification']);
        });

        // Seed: previos start at 6675, folios at 6025
        $classifications = ['A', 'B', 'C', 'D'];
        foreach ($classifications as $c) {
            DB::table('folio_code_sequences')->insert([
                'type' => 'previo',
                'classification' => $c,
                'last_number' => 6674,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('folio_code_sequences')->insert([
                'type' => 'folio',
                'classification' => $c,
                'last_number' => 6024,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folio_code_sequences');
    }
};

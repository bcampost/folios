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
        Schema::table('folios', function (Blueprint $table) {
            $table->unsignedInteger('code_number')->nullable()->after('classification')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('folios', function (Blueprint $table) {
            $table->dropColumn('code_number');
        });
    }
};

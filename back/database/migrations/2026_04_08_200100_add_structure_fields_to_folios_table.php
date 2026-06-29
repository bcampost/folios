<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folios', function (Blueprint $table) {
            $table->boolean('cambia_estructura')->default(false)->after('description');
            $table->boolean('cambia_materiales')->default(false)->after('cambia_estructura');
            $table->boolean('cambia_herrajes')->default(false)->after('cambia_materiales');
            $table->boolean('cambia_proceso')->default(false)->after('cambia_herrajes');
            $table->text('detalle_estructura')->nullable()->after('cambia_proceso');
        });
    }

    public function down(): void
    {
        Schema::table('folios', function (Blueprint $table) {
            $table->dropColumn([
                'cambia_estructura',
                'cambia_materiales',
                'cambia_herrajes',
                'cambia_proceso',
                'detalle_estructura',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Use the highest last_number across classifications as the new global value
        $previoMax = DB::table('folio_code_sequences')->where('type', 'previo')->max('last_number') ?? 6674;
        $folioMax = DB::table('folio_code_sequences')->where('type', 'folio')->max('last_number') ?? 6024;

        // Drop old unique constraint if it exists
        $hasOldUnique = DB::selectOne(
            "SELECT COUNT(*) as cnt FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'folio_code_sequences'
             AND INDEX_NAME = 'folio_code_sequences_type_classification_unique'"
        );
        if ($hasOldUnique && $hasOldUnique->cnt > 0) {
            Schema::table('folio_code_sequences', function (Blueprint $table) {
                $table->dropUnique(['type', 'classification']);
            });
        }

        DB::table('folio_code_sequences')->truncate();

        // Drop classification column if it still exists
        if (Schema::hasColumn('folio_code_sequences', 'classification')) {
            Schema::table('folio_code_sequences', function (Blueprint $table) {
                $table->dropColumn('classification');
            });
        }

        // Add unique on type if not already present
        $hasTypeUnique = DB::selectOne(
            "SELECT COUNT(*) as cnt FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'folio_code_sequences'
             AND INDEX_NAME = 'folio_code_sequences_type_unique'"
        );
        if (!$hasTypeUnique || $hasTypeUnique->cnt == 0) {
            Schema::table('folio_code_sequences', function (Blueprint $table) {
                $table->unique('type');
            });
        }

        DB::table('folio_code_sequences')->insert([
            ['type' => 'previo', 'last_number' => $previoMax, 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'folio', 'last_number' => $folioMax, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::table('folio_code_sequences', function (Blueprint $table) {
            $table->dropUnique(['type']);
            $table->string('classification')->after('type');
            $table->unique(['type', 'classification']);
        });

        DB::table('folio_code_sequences')->truncate();

        $classifications = ['A', 'B', 'C', 'D'];
        foreach ($classifications as $c) {
            DB::table('folio_code_sequences')->insert([
                'type' => 'previo', 'classification' => $c, 'last_number' => 6674, 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('folio_code_sequences')->insert([
                'type' => 'folio', 'classification' => $c, 'last_number' => 6024, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
};

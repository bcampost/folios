<?php

use App\Enums\FolioTypeEnum;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('folios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained();
            $table->string('type')->index()->default(FolioTypeEnum::Previo->value);
            $table->integer('assembly_number')->nullable();
            $table->string('previo_code')->nullable();
            $table->string('folio_code')->nullable();
            $table->foreignId('state')->constrained('folio_states');
            $table->string('classification')->index()->nullable();
            $table->bigInteger('reference_product')->index()->nullable();
            $table->smallInteger('quantity');
            $table->string('height')->nullable();
            $table->string('width')->nullable();
            $table->string('depth')->nullable();
            $table->string('melamina_color')->nullable();
            $table->string('melamina_density')->nullable();
            $table->string('chapacinta_color')->nullable();
            $table->string('structure_color')->nullable();
            $table->string('tela_color')->nullable();
            $table->string('package_type')->nullable();
            $table->decimal('cost', 16, 2)->nullable();
            $table->text('cost_details')->nullable();
            $table->decimal('list_price', 16, 2)->nullable();
            $table->text('description')->nullable();
            $table->text('comments')->nullable();
            $table->string('reason_for_rejection')->nullable();
            $table->string('acabados')->nullable();
            $table->json('screw_kits')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folios');
    }
};

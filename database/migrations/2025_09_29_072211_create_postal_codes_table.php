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
        // Keep existing table/data if already loaded from CSV.
        if (Schema::hasTable('postal_codes')) {
            return;
        }

        Schema::create('postal_codes', function (Blueprint $table) {
            $table->id();
            $table->string('zip', 10)->index();
            $table->string('city');
            $table->foreignId('county_id')->constrained('counties')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['zip', 'city']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postal_codes');
    }
};

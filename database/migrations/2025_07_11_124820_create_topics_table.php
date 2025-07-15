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
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            // Foreign key to the modules table, on delete cascade ensures topics are removed if module is deleted
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();

            // Ensure topic name is unique within a specific module
            $table->unique(['module_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};

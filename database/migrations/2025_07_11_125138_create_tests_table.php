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
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('test_code')->unique(); // Unique code for accessing the test
            $table->integer('duration_minutes'); // Overall test duration
            $table->boolean('is_enabled')->default(true); // Is the test active/available?
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->integer('total_questions')->default(0); // Can be updated after test configuration
            $table->integer('pass_percentage')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};

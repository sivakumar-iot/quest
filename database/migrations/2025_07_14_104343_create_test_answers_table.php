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
        Schema::create('test_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_attempt_id')->constrained('test_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->json('selected_options')->nullable(); // Stores selected option(s) as JSON
            $table->boolean('is_correct')->nullable(); // Nullable initially, calculated on submission
            $table->timestamps();

            // Ensure a student answers a specific question only once per attempt
            $table->unique(['test_attempt_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_answers');
    }
};

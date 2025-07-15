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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->foreignId('topic_id')->constrained('topics')->onDelete('cascade');
            $table->text('question_text');
            // e.g., 'multiple_choice', 'yes_no', 'short_answer'
            $table->string('question_type');
            $table->json('options')->nullable(); // Stores options as a JSON array (e.g., ["A", "B", "C", "D"])
            $table->json('correct_answer'); // Stores correct answer(s) as JSON (e.g., "A" or ["A", "C"])
            $table->boolean('timer_enabled')->default(false);
            $table->integer('timer_value')->default(0); // Time in seconds for this question
            $table->boolean('is_enabled')->default(true); // Is the question active/available?
            $table->boolean('is_random_options')->default(false); // Should options be randomized when presented?
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};

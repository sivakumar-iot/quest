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
        Schema::create('test_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('tests')->onDelete('cascade');
            $table->foreignId('module_id')->nullable()->constrained('modules')->onDelete('cascade');
            $table->foreignId('topic_id')->nullable()->constrained('topics')->onDelete('cascade');
            $table->string('question_type')->nullable(); // e.g., 'multiple_choice', 'yes_no'
            $table->integer('number_of_questions'); // How many questions to pick based on this rule
            $table->string('difficulty_level')->nullable(); // Optional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_rules');
    }
};

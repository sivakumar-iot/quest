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
        Schema::create('test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('tests')->onDelete('cascade');
            // Student details (assuming unauthenticated students for now)
            $table->string('student_name');
            $table->string('father_name')->nullable();
            $table->date('dob')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable(); // Can be unique if needed, but not required for unauthenticated
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('score')->nullable();
            $table->integer('total_questions_answered')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_attempts');
    }
};

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'test_code',
        'duration_minutes',
        'is_enabled',
        'description',
        'instructions',
        'total_questions', // This will be calculated and updated by controller
        'pass_percentage',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * Get the rules for generating questions for the test.
     */
    public function rules() // Renamed from configurations()
    {
        return $this->hasMany(TestRule::class); // Use TestRule model
    }

    /**
     * Get the actual questions selected for this test instance.
     */
    public function testQuestions()
    {
        return $this->hasMany(TestQuestion::class)->orderBy('question_order');
    }

    /**
     * Get the test attempts for the test.
     */
    public function testAttempts()
    {
        return $this->hasMany(TestAttempt::class);
    }
}

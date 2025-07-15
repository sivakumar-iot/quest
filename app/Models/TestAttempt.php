<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'student_name',
        'father_name',
        'dob',
        'mobile',
        'email',
        'started_at',
        'completed_at',
        'score',
        'total_questions_answered',
        'is_completed',
    ];

    protected $casts = [
        'dob' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
    ];

    /**
     * Get the test associated with the attempt.
     */
    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    /**
     * Get the answers for the test attempt.
     */
    public function answers()
    {
        return $this->hasMany(TestAnswer::class);
    }
}

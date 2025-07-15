<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_attempt_id',
        'question_id',
        'selected_options',
        'is_correct',
    ];

    protected $casts = [
        'selected_options' => 'array', // Cast to array for JSON storage
        'is_correct' => 'boolean',
    ];

    /**
     * Get the test attempt that owns the answer.
     */
    public function testAttempt()
    {
        return $this->belongsTo(TestAttempt::class);
    }

    /**
     * Get the question associated with the answer.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}

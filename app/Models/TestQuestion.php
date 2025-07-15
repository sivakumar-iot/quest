<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'question_id',
        'question_order',
    ];

    /**
     * Get the test that this question belongs to.
     */
    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    /**
     * Get the actual question details.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}

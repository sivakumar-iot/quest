<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'module_id',
        'topic_id',
        'question_text',
        'question_type',
        'options',
        'correct_answer',
        'timer_enabled',
        'timer_value',
        'is_enabled',
        'is_random_options',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'options' => 'array', // Casts the 'options' column to a PHP array
        'correct_answer' => 'array', // Casts the 'correct_answer' column to a PHP array
        'timer_enabled' => 'boolean', // Casts to boolean
        'is_enabled' => 'boolean', // Casts to boolean
        'is_random_options' => 'boolean', // Casts to boolean
    ];

    /**
     * Get the module that owns the question.
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get the topic that owns the question.
     */
    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }
}

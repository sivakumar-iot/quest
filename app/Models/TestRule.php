<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestRule extends Model
{
    use HasFactory;

    // Specify the table name as it no longer matches the model name
    protected $table = 'test_rules';

    protected $fillable = [
        'test_id',
        'module_id',
        'topic_id',
        'question_type',
        'number_of_questions',
        'difficulty_level',
    ];

    /**
     * Get the test that owns the rule.
     */
    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    /**
     * Get the module associated with the rule.
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get the topic associated with the rule.
     */
    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }
}

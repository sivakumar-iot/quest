<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the topics for the module.
     */
    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    /**
     * Get the questions for the module.
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}

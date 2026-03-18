<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    protected $fillable = [
        'title',
        'description',
        'is_done',
    ];

    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
        ];
    }
}


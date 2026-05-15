<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    protected $fillable = [
        'title',
        'description',
        'priority',
        'category',
        'status',
        'summary',
        'suggested_action',
        'escalated',
        'due_at',
    ];

    protected $casts = [
        'escalated' => 'boolean',
        'due_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'open',
        'priority' => 'medium',
        'escalated' => false,
    ];
}

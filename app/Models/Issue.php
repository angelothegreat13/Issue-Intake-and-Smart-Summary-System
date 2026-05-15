<?php

namespace App\Models;

use App\Enums\Category;
use App\Enums\Priority;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    use HasFactory;
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
        'priority'  => Priority::class,
        'status'    => Status::class,
        'category'  => Category::class,
        'escalated' => 'boolean',
        'due_at'    => 'datetime',
    ];

    protected $attributes = [
        'status'    => 'open',
        'priority'  => 'medium',
        'escalated' => false,
    ];
}

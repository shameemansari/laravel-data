<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    use HasFactory;

    protected $table = 'records';

    protected $fillable = [
        'name',
        'bio',
        'age',
        'contact',
        'is_active',
        'born_on',
    ];

    protected $casts = [
        'name' => 'string',
        'bio' => 'string',
        'age' => 'integer',
        'contact' => 'string',
        'is_active' => 'boolean',
        'born_on' => 'datetime:Y-m-d',
    ];
}

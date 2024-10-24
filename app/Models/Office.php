<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
        'radius' => 'integer',
    ];

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'radius',
    ];
}

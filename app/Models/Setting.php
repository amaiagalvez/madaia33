<?php

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'key',
        'value',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];
}

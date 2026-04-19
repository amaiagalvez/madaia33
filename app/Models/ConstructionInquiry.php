<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Database\Factories\ConstructionInquiryFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConstructionInquiry extends Model
{
    /** @use HasFactory<ConstructionInquiryFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'construction_id',
        'user_id',
        'name',
        'email',
        'subject',
        'message',
        'reply',
        'replied_at',
        'is_read',
        'read_at',
    ];

    protected static function newFactory(): ConstructionInquiryFactory
    {
        return ConstructionInquiryFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'construction_id' => 'integer',
            'user_id' => 'integer',
            'is_read' => 'boolean',
            'replied_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Construction, $this>
     */
    public function construction(): BelongsTo
    {
        return $this->belongsTo(Construction::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

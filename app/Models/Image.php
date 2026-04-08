<?php

namespace App\Models;

use Database\Factories\ImageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\ResolvesLocalizedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Image extends Model
{
    /** @use HasFactory<ImageFactory> */
    use HasFactory;

    use ResolvesLocalizedAttributes;
    use SoftDeletes;

    protected $fillable = [
        'filename',
        'path',
        'alt_text_eu',
        'alt_text_es',
    ];

    /**
     * Bilingual accessor for alt_text with fallback.
     */
    public function getAltTextAttribute(): string
    {
        return $this->resolveLocalizedAttribute('alt_text');
    }

    /**
     * Public URL with fallback to avoid broken image requests.
     */
    public function getPublicUrlAttribute(): string
    {
        if (filled($this->path) && Storage::disk('public')->exists($this->path)) {
            return Storage::url($this->path);
        }

        return asset('favicon.svg');
    }
}

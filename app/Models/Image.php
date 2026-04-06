<?php

namespace App\Models;

use Database\Factories\ImageFactory;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Image extends Model
{
    /** @use HasFactory<ImageFactory> */
    use HasFactory, SoftDeletes;

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
        $locale = App::getLocale();

        return $this->{"alt_text_{$locale}"} ?? $this->alt_text_eu ?? $this->alt_text_es ?? '';
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

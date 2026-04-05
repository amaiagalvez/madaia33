<?php

namespace App\Models;

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Image extends Model
{
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
}

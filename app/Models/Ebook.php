<?php

namespace App\Models;

use App\Enums\EbookStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ebook extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'title', 'subtitle', 'description', 'audience', 'contents', 'author_name', 'publisher_name', 'price_kobo', 'currency',
        'cover_path', 'pdf_path', 'free_chapter_path', 'free_chapter_content', 'cheat_sheet_path',
        'meta_title', 'meta_description', 'primary_keyword', 'faq', 'status',
    ];

    protected $casts = [
        'price_kobo' => 'integer',
        'faq' => 'array',
        'status' => EbookStatus::class,
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(EbookOrder::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(EbookEvent::class);
    }
}

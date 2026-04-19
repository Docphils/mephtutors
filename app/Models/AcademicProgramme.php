<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicProgramme extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'tagline',
        'summary',
        'overview',
        'who_it_is_for',
        'what_parents_can_expect',
        'starting_from_text',
        'pricing_note',
        'renewability_note',
        'frequency_options',
        'duration_options',
        'mode_options',
        'subject_options',
        'max_selectable_subjects',
        'pricing_matrix',
        'faq_items',
        'sort_order',
        'is_active',
        'meta_title',
        'meta_description',
        'og_image',
        'hero_image',
        'service_item_id',
    ];

    protected $casts = [
        'frequency_options' => 'array',
        'duration_options' => 'array',
        'mode_options' => 'array',
        'subject_options' => 'array',
        'pricing_matrix' => 'array',
        'faq_items' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'max_selectable_subjects' => 'integer',
    ];

    public function serviceItem(): BelongsTo
    {
        return $this->belongsTo(ServiceItem::class);
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(ProgrammeEnquiry::class);
    }
}

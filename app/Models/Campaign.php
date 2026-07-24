<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'status',
        'summary',
        'description',
        'image',
        'date_label',
        'date',
        'location',
        'year',
        'supporters_label',
        'signatures_label',
        'cta_text',
        'link',
        'signup_url',
        'meta_title',
        'meta_description',
        'published_at',
        'scheduled_at'
    ];

    protected $casts = [
        'date' => 'date',
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($campaign) {
            if (empty($campaign->uuid)) {
                $campaign->uuid = (string) Str::uuid();
            }
        });
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(CampaignCategory::class, 'campaign_category_assignments', 'campaign_id', 'category_id')
            ->withPivot(['order', 'featured', 'featured_order'])
            ->withTimestamps();
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeInCategory($query, $categorySlug)
    {
        return $query->whereHas('categories', function($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    public function scopeOrderedInCategory($query, $categorySlug)
    {
        return $query->join('campaign_category_assignments', 'campaigns.id', '=', 'campaign_category_assignments.campaign_id')
            ->join('campaign_categories', 'campaign_category_assignments.category_id', '=', 'campaign_categories.id')
            ->where('campaign_categories.slug', $categorySlug)
            ->select('campaigns.*', 'campaign_category_assignments.order', 'campaign_category_assignments.featured', 'campaign_category_assignments.featured_order')
            ->orderBy('campaign_category_assignments.order', 'asc');
    }

    public function scopeFeaturedInCategory($query, $categorySlug)
    {
        return $query->join('campaign_category_assignments', 'campaigns.id', '=', 'campaign_category_assignments.campaign_id')
            ->join('campaign_categories', 'campaign_category_assignments.category_id', '=', 'campaign_categories.id')
            ->where('campaign_categories.slug', $categorySlug)
            ->where('campaign_category_assignments.featured', true)
            ->whereNotNull('campaign_category_assignments.featured_order')
            ->select('campaigns.*', 'campaign_category_assignments.featured_order')
            ->orderBy('campaign_category_assignments.featured_order', 'asc');
    }
}

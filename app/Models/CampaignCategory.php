<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CampaignCategory extends Model
{
    protected $fillable = ['slug', 'name', 'display_order'];

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campaign_category_assignments', 'category_id', 'campaign_id')
            ->withPivot(['order', 'featured', 'featured_order'])
            ->withTimestamps();
    }
}

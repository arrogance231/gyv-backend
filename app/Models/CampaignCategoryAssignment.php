<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CampaignCategoryAssignment extends Pivot
{
    protected $table = 'campaign_category_assignments';

    protected $fillable = [
        'campaign_id',
        'category_id',
        'order',
        'featured',
        'featured_order',
    ];
}

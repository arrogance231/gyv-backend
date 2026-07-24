<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Campaign;

class CampaignPolicy
{
    /**
     * Determine whether the user can view any campaigns.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('edit-site-sections') || $user->hasPermission('publish-content');
    }

    /**
     * Determine whether the user can view the campaign.
     */
    public function view(User $user, Campaign $campaign): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create campaigns.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('edit-site-sections');
    }

    /**
     * Determine whether the user can update the campaign.
     */
    public function update(User $user, Campaign $campaign): bool
    {
        return $user->hasPermission('edit-site-sections');
    }

    /**
     * Determine whether the user can delete the campaign.
     */
    public function delete(User $user, Campaign $campaign): bool
    {
        return $user->hasPermission('edit-site-sections');
    }

    /**
     * Determine whether the user can restore the campaign.
     */
    public function restore(User $user, Campaign $campaign): bool
    {
        return $user->hasPermission('edit-site-sections');
    }

    /**
     * Determine whether the user can publish the campaign.
     */
    public function publish(User $user, Campaign $campaign): bool
    {
        return $user->hasPermission('publish-content');
    }
}

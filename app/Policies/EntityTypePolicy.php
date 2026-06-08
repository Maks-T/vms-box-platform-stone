<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Nicole\Box\Core\Models\EntityType;
use Illuminate\Auth\Access\HandlesAuthorization;

class EntityTypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EntityType');
    }

    public function view(AuthUser $authUser, EntityType $entityType): bool
    {
        return $authUser->can('View:EntityType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EntityType');
    }

    public function update(AuthUser $authUser, EntityType $entityType): bool
    {
        return $authUser->can('Update:EntityType');
    }

    public function delete(AuthUser $authUser, EntityType $entityType): bool
    {
        return $authUser->can('Delete:EntityType');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EntityType');
    }

    public function restore(AuthUser $authUser, EntityType $entityType): bool
    {
        return $authUser->can('Restore:EntityType');
    }

    public function forceDelete(AuthUser $authUser, EntityType $entityType): bool
    {
        return $authUser->can('ForceDelete:EntityType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EntityType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EntityType');
    }

    public function replicate(AuthUser $authUser, EntityType $entityType): bool
    {
        return $authUser->can('Replicate:EntityType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EntityType');
    }

}
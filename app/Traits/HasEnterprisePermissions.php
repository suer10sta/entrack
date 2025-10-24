<?php

namespace App\Traits;

use App\Models\EnterpriseRolePermission;

trait HasEnterprisePermissions
{
    public function enterprisePermissions()
    {
        return $this->hasMany(EnterpriseRolePermission::class, 'role_id');
    }

    public function hasEnterprisePermission($permission)
    {
        return $this->roles()
            ->whereHas('enterprisePermissions', function ($query) use ($permission) {
                $query->where('entreprise_id', $this->entreprise_id)
                    ->whereHas('permission', function ($q) use ($permission) {
                        $q->where('name', $permission);
                    });
            })
            ->exists();
    }
}

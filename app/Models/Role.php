<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = ['name', 'guard_name', 'entreprise_id'];

    public function enterprisePermissions()
    {
        return $this->hasMany(EnterpriseRolePermission::class, 'role_id');
    }
}

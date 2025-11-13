<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnterpriseRolePermission extends Model
{
    protected $fillable = 
    [
        'entreprise_id', 
        'role_id', 
        'permission_id'
    ];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}

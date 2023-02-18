<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    use HasFactory;

    // protected $hidden = ['id'];

    public function getActions($id): HasMany
    {
        return $this->hasMany(RolePermission::class, 'permission_id')->where('role_id', $id)->pluck('action')->toArray();
    }

}

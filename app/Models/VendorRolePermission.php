<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorRolePermission extends Model
{
    protected $table = 'vendor_role_permissions';

    protected $fillable = [
        'role_id',
        'permission_id',
    ];
}

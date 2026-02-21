<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use App\Models\ShippingMethod;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShippingMethodPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        $permission = Permission::where('name', 'shipping_methods')->first();
        return $user->hasRole($permission->roles);
    }

    public function view(User $user)
    {
        $permission = Permission::where('name', 'shipping_methods')->first();
        return $user->hasRole($permission->roles);
    }

    public function create(User $user)
    {
        $permission = Permission::where('name', 'shipping_methods')->first();
        return $user->hasRole($permission->roles);
    }


    public function update(User $user)
    {
        $permission = Permission::where('name', 'shipping_methods')->first();
        return $user->hasRole($permission->roles);
    }

    public function delete(User $user)
    {
        $permission = Permission::where('name', 'shipping_methods')->first();
        return $user->hasRole($permission->roles);
    }
}

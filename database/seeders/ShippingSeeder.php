<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ShippingMethod;

class ShippingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Create Permissions
        $permissions = [
            'shipping_settings', // Parent menu
            'shipping_companies',
            'shipping_methods',
        ];

        // Get max ID to manually increment if needed, or rely on auto-increment.
        // PermissionsSeeder used manual IDs, so we should be careful.
        // Let's find the max ID currently in permissions table.
        $maxId = DB::table('permissions')->max('id') ?? 0;

        foreach ($permissions as $permission) {
             $exists = DB::table('permissions')->where('name', $permission)->exists();
             if (!$exists) {
                $maxId++;
                DB::table('permissions')->insert([
                    'id' => $maxId,
                    'name' => $permission,
                    // 'guard_name' => 'web', // Removed as column doesn't exist
                    // 'created_at' => now(), // Removed as implied not needed/existing
                    // 'updated_at' => now(),
                ]);
             }
        }

        // 2. Assign to Owner Role (ID 1)
        // We need to associate these permissions with Role ID 1 in permission_role table.
        // First get the IDs of the permissions we just ensured exist.
        $permissionIds = DB::table('permissions')->whereIn('name', $permissions)->pluck('id');
        $roleId = 1; // Owner

        foreach ($permissionIds as $permId) {
            $exists = DB::table('permission_role')
                ->where('permission_id', $permId)
                ->where('role_id', $roleId)
                ->exists();
            
            if (!$exists) {
                DB::table('permission_role')->insert([
                    'permission_id' => $permId,
                    'role_id' => $roleId,
                ]);
            }
        }

        // 3. Seed "Store Pickup" Method
        $storePickup = ShippingMethod::where('name', 'Store Pickup')->first();
        if (!$storePickup) {
            ShippingMethod::create([
                'name' => 'Store Pickup',
                'is_active' => true,
                'shipping_company_id' => null, // Not linked to any external company
            ]);
        }
    }
}

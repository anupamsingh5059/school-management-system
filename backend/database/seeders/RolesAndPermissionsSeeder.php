<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\School;
use Carbon\Carbon;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Roles
        $roles = [
            ['name' => 'super_admin', 'guard_name' => 'web', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'admin', 'guard_name' => 'web', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'user', 'guard_name' => 'web', 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(['name' => $role['name']], $role);
        }

        // Example permissions (you can expand these later)
        $permissions = [
            ['name' => 'manage_users', 'guard_name' => 'web', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'manage_vendors', 'guard_name' => 'web', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'view_reports', 'guard_name' => 'web', 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($permissions as $perm) {
            DB::table('permissions')->updateOrInsert(['name' => $perm['name']], $perm);
        }

        // Attach permissions to roles using pivot table
        $roleIds = DB::table('roles')->whereIn('name', ['super_admin', 'admin', 'user'])->pluck('id', 'name');
        $permIds = DB::table('permissions')->pluck('id', 'name');

        // super_admin -> all permissions
        foreach ($permIds as $permName => $pid) {
            DB::table('permission_role')->updateOrInsert([
                'permission_id' => $pid,
                'role_id' => $roleIds['super_admin'],
            ], []);
        }

        // admin -> manage_users, manage_vendors
        foreach (['manage_users', 'manage_vendors'] as $p) {
            if (isset($permIds[$p])) {
                DB::table('permission_role')->updateOrInsert([
                    'permission_id' => $permIds[$p],
                    'role_id' => $roleIds['admin'],
                ], []);
            }
        }

        // user -> view_reports
        if (isset($permIds['view_reports'])) {
            DB::table('permission_role')->updateOrInsert([
                'permission_id' => $permIds['view_reports'],
                'role_id' => $roleIds['user'],
            ], []);
        }

        // Assign super_admin to the default test user if present
        $testUser = User::where('email', 'test@example.com')->first();
        // Create a default school (tenant) and attach to test user
        $school = School::firstOrCreate([
            'slug' => 'default-school',
        ], [
            'name' => 'Default School',
            'address' => null,
            'phone' => null,
            'active' => true,
        ]);

        if ($testUser && isset($roleIds['super_admin'])) {
            $testUser->assignRole('super_admin');
            // set school for the test user if not set
            if (! $testUser->school_id) {
                $testUser->school_id = $school->id;
                $testUser->save();
            }
        }
    }
}

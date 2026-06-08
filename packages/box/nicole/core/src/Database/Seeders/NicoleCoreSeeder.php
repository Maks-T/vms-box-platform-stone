<?php

declare(strict_types=1);

namespace Nicole\Box\Core\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Nicole\Box\Core\Models\Channel;
use Nicole\Box\Core\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class NicoleCoreSeeder extends Seeder
{
    public function run(): void
    {
        // Clear Spatie Cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 1. Create Super Admin Role
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'guard_name' => 'web',
            ],
        );

        // 2. Create Platform Super Admin
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@vms.local'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
            ],
        );

        // 3. Assign role to user
        $adminUser->assignRole($adminRole);

        Channel::updateOrCreate(
            ['code' => 'widget'],
            [
                'name' => ['ru' => 'Виджет калькулятора', 'en' => 'Calculator Widget'],
                'is_active' => true,
            ],
        );

        Channel::updateOrCreate(
            ['code' => 'catalog'],
            [
                'name' => ['ru' => 'Основной сайт', 'en' => 'Main Web Catalog'],
                'is_active' => true,
            ],
        );

        $this->command->info(
            'VMS-NC Box (Stone) Seeded successfully. Super Admin created (admin@vms.local / password).',
        );
    }
}

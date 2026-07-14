<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'dashboard.basic',

            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            'settings.view',
            'settings.edit',

            'media.view',
            'media.create',
            'media.edit',
            'media.delete',

            'menus.view',
            'menus.create',
            'menus.edit',
            'menus.delete',

            'pages.view',
            'pages.create',
            'pages.edit',
            'pages.delete',
            'pages.publish',

            'posts.view',
            'posts.create',
            'posts.edit',
            'posts.delete',
            'posts.publish',

            'events.view',
            'events.create',
            'events.edit',
            'events.delete',
            'events.publish',

            'calls.view',
            'calls.create',
            'calls.edit',
            'calls.delete',
            'calls.publish',

            'bulletins.view',
            'bulletins.create',
            'bulletins.edit',
            'bulletins.delete',
            'bulletins.publish',

            'journals.view',
            'journals.create',
            'journals.edit',
            'journals.delete',

            'articles.view',
            'articles.create',
            'articles.edit',
            'articles.delete',
            'articles.publish',
            'articles.submit',

            'researchers.view',
            'researchers.create',
            'researchers.edit',
            'researchers.delete',

            'notifications.view',
            'notifications.create',
            'notifications.send',

            'seo.view',
            'seo.edit',

            'profile.view',
            'profile.edit',

            'research.view',
            'research.create',
            'research.edit',

            'dashboard.researcher',

            'applications.create',
            'applications.view-own',
            'applications.edit-own',
            'applications.submit',
            'applications.withdraw',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::firstOrCreate([
            'name' => 'ADMINISTRADOR',
            'guard_name' => 'web',
        ]);

        $webmaster = Role::firstOrCreate([
            'name' => 'WEBMASTER',
            'guard_name' => 'web',
        ]);

        $researcher = Role::firstOrCreate([
            'name' => 'INVESTIGADOR',
            'guard_name' => 'web',
        ]);

        $user = Role::firstOrCreate([
            'name' => 'USUARIO',
            'guard_name' => 'web',
        ]);

        $admin->syncPermissions(Permission::all());

        $webmaster->syncPermissions([
            'dashboard.view',

            'settings.view',

            'media.view',
            'media.create',
            'media.edit',
            'media.delete',

            'menus.view',
            'menus.create',
            'menus.edit',
            'menus.delete',

            'pages.view',
            'pages.create',
            'pages.edit',
            'pages.delete',
            'pages.publish',

            'posts.view',
            'posts.create',
            'posts.edit',
            'posts.delete',
            'posts.publish',

            'events.view',
            'events.create',
            'events.edit',
            'events.delete',
            'events.publish',

            'calls.view',
            'calls.create',
            'calls.edit',
            'calls.delete',
            'calls.publish',

            'bulletins.view',
            'bulletins.create',
            'bulletins.edit',
            'bulletins.delete',
            'bulletins.publish',

            'journals.view',
            'journals.create',
            'journals.edit',
            'journals.delete',

            'articles.view',
            'articles.create',
            'articles.edit',
            'articles.delete',
            'articles.publish',

            'researchers.view',
            'researchers.create',
            'researchers.edit',

            'notifications.view',
            'notifications.create',
            'notifications.send',

            'seo.view',
            'seo.edit',

            'profile.view',
            'profile.edit',
        ]);

        $researcher->syncPermissions([
            'dashboard.researcher',

            'profile.view',
            'profile.edit',

            'research.view',
            'research.create',
            'research.edit',

            'articles.submit',

            'events.view',
            'calls.view',
            'notifications.view',
        ]);

        $user->syncPermissions([
            'dashboard.basic',
            'profile.view',
            'profile.edit',
            'applications.create',
            'applications.view-own',
            'applications.edit-own',
            'applications.submit',
            'applications.withdraw',
        ]);

        $firstUser = User::first();

        if ($firstUser && !$firstUser->hasRole('ADMINISTRADOR')) {
            $firstUser->assignRole('ADMINISTRADOR');
        }
    }
}

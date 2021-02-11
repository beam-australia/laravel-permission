---
title: Database Seeding
weight: 2
---

## Flush cache before seeding

You may discover that it is best to flush this package's cache before seeding, to avoid cache conflict errors. 

```php
// reset cached groups and permissions
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
```

You can do this in the `SetUp()` method of your test suite (see the Testing page in the docs).

Or it can be done directly in a seeder class, as shown below.

Here is a sample seeder, which first clears the cache, creates permissions and then assigns permissions to groups (the order of these steps is intentional):

```php
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Group;
use Spatie\Permission\Models\Permission;

class GroupsAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached groups and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'edit articles']);
        Permission::create(['name' => 'delete articles']);
        Permission::create(['name' => 'publish articles']);
        Permission::create(['name' => 'unpublish articles']);

        // create groups and assign created permissions

        // this can be done as separate statements
        $group = Group::create(['name' => 'writer']);
        $group->givePermissionTo('edit articles');

        // or may be done by chaining
        $group = Group::create(['name' => 'moderator'])
            ->givePermissionTo(['publish articles', 'unpublish articles']);

        $group = Group::create(['name' => 'super-admin']);
        $group->givePermissionTo(Permission::all());
    }
}
```

## Speeding up seeding for large data sets

When seeding large quantities of groups or permissions you may consider using Eloquent's `insert` command instead of `create`, as this bypasses all the internal checks that this package does when calling `create` (including extra queries to verify existence, test guards, etc).

```php
    $arrayOfPermissionNames = ['writer', 'editor'];
    $permissions = collect($arrayOfPermissionNames)->map(function ($permission) {
        return ['name' => $permission, 'guard_name' => 'web'];
    });

    Permission::insert($permissions->toArray());
```

Alternatively you could use `DB::insert`, as long as you also provide all the required data fields. One example of this is shown below ... but note that this example hard-codes the table names and field names, thus does not respect any customizations you may have in your permissions config file.

```php
$permissionsByGroup = [
    'admin' => ['restore posts', 'force delete posts'],
    'editor' => ['create a post', 'update a post', 'delete a post'],
    'viewer' => ['view all posts', 'view a post']
];

$insertPermissions = fn ($group) => collect($permissionsByGroup[$group])
    ->map(fn ($name) => DB::table()->insertGetId(['name' => $name]))
    ->toArray();

$permissionIdsByGroup = [
    'admin' => $insertPermissions('admin'),
    'editor' => $insertPermissions('editor'),
    'viewer' => $insertPermissions('viewer')
];

foreach ($permissionIdsByGroup as $group => $permissionIds) {
    $group = Group::whereName($group)->first();

    DB::table('group_has_permissions')
        ->insert(
            collect($permissionIds)->map(fn ($id) => [
                'group_id' => $group->id,
                'permission_id' => $id
            ])->toArray()
        );
}
```

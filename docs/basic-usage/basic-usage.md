---
title: Basic Usage
weight: 1
---

First, add the `Spatie\Permission\Traits\HasGroups` trait to your `User` model(s):

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasGroups;

class User extends Authenticatable
{
    use HasGroups;

    // ...
}
```

This package allows for users to be associated with permissions and groups. Every group is associated with multiple permissions.
A `Group` and a `Permission` are regular Eloquent models. They require a `name` and can be created like this:

```php
use Spatie\Permission\Models\Group;
use Spatie\Permission\Models\Permission;

$group = Group::create(['name' => 'writer']);
$permission = Permission::create(['name' => 'edit articles']);
```


A permission can be assigned to a group using 1 of these methods:

```php
$group->givePermissionTo($permission);
$permission->assignGroup($group);
```

Multiple permissions can be synced to a group using 1 of these methods:

```php
$group->syncPermissions($permissions);
$permission->syncGroups($groups);
```

A permission can be removed from a group using 1 of these methods:

```php
$group->revokePermissionTo($permission);
$permission->removeGroup($group);
```

If you're using multiple guards the `guard_name` attribute needs to be set as well. Read about it in the [using multiple guards](../multiple-guards) section of the readme.

The `HasGroups` trait adds Eloquent relationships to your models, which can be accessed directly or used as a base query:

```php
// get a list of all permissions directly assigned to the user
$permissionNames = $user->getPermissionNames(); // collection of name strings
$permissions = $user->permissions; // collection of permission objects

// get all permissions for the user, either directly, or from groups, or from both
$permissions = $user->getDirectPermissions();
$permissions = $user->getPermissionsViaGroups();
$permissions = $user->getAllPermissions();

// get the names of the user's groups
$groups = $user->getGroupNames(); // Returns a collection
```

The `HasGroups` trait also adds a `group` scope to your models to scope the query to certain groups or permissions:

```php
$users = User::group('writer')->get(); // Returns only users with the group 'writer'
```

The `group` scope can accept a string, a `\Spatie\Permission\Models\Group` object or an `\Illuminate\Support\Collection` object.

The same trait also adds a scope to only get users that have a certain permission.

```php
$users = User::permission('edit articles')->get(); // Returns only users with the permission 'edit articles' (inherited or directly)
```

The scope can accept a string, a `\Spatie\Permission\Models\Permission` object or an `\Illuminate\Support\Collection` object.


### Eloquent
Since Group and Permission models are extended from Eloquent models, basic Eloquent calls can be used as well:

```php
$all_users_with_all_their_groups = User::with('groups')->get();
$all_users_with_all_direct_permissions = User::with('permissions')->get();
$all_groups_in_database = Group::all()->pluck('name');
$users_without_any_groups = User::doesntHave('groups')->get();
$all_groups_except_a_and_b = Group::whereNotIn('name', ['group A', 'group B'])->get();
```


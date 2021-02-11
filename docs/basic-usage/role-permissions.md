---
title: Using Permissions via Groups
weight: 3
---

## Assigning Groups

A group can be assigned to any user:

```php
$user->assignGroup('writer');

// You can also assign multiple groups at once
$user->assignGroup('writer', 'admin');
// or as an array
$user->assignGroup(['writer', 'admin']);
```

A group can be removed from a user:

```php
$user->removeGroup('writer');
```

Groups can also be synced:

```php
// All current groups will be removed from the user and replaced by the array given
$user->syncGroups(['writer', 'admin']);
```

## Checking Groups

You can determine if a user has a certain group:

```php
$user->hasGroup('writer');

// or at least one group from an array of groups:
$user->hasGroup(['editor', 'moderator']);
```

You can also determine if a user has any of a given list of groups:

```php
$user->hasAnyGroup(['writer', 'reader']);
// or
$user->hasAnyGroup('writer', 'reader');
```

You can also determine if a user has all of a given list of groups:

```php
$user->hasAllGroups(Group::all());
```

The `assignGroup`, `hasGroup`, `hasAnyGroup`, `hasAllGroups`  and `removeGroup` functions can accept a
 string, a `\Spatie\Permission\Models\Group` object or an `\Illuminate\Support\Collection` object.


## Assigning Permissions to Groups

A permission can be given to a group:

```php
$group->givePermissionTo('edit articles');
```

You can determine if a group has a certain permission:

```php
$group->hasPermissionTo('edit articles');
```

A permission can be revoked from a group:

```php
$group->revokePermissionTo('edit articles');
```

The `givePermissionTo` and `revokePermissionTo` functions can accept a
string or a `Spatie\Permission\Models\Permission` object.


**Permissions are inherited from groups automatically.**


## Assigning Direct Permissions To A User

Additionally, individual permissions can be assigned to the user too. 
For instance:

```php
$group = Group::findByName('writer');
$group->givePermissionTo('edit articles');

$user->assignGroup('writer');

$user->givePermissionTo('delete articles');
```

In the above example, a group is given permission to edit articles and this group is assigned to a user. 
Now the user can edit articles and additionally delete articles. The permission of 'delete articles' is the user's direct permission because it is assigned directly to them.
When we call `$user->hasDirectPermission('delete articles')` it returns `true`, 
but `false` for `$user->hasDirectPermission('edit articles')`.

This method is useful if one builds a form for setting permissions for groups and users in an application and wants to restrict or change inherited permissions of groups of the user, i.e. allowing to change only direct permissions of the user.


You can check if the user has a Specific or All or Any of a set of permissions directly assigned:

```php
// Check if the user has Direct permission
$user->hasDirectPermission('edit articles')

// Check if the user has All direct permissions
$user->hasAllDirectPermissions(['edit articles', 'delete articles']);

// Check if the user has Any permission directly
$user->hasAnyDirectPermission(['create articles', 'delete articles']);
```
By following the previous example, when we call `$user->hasAllDirectPermissions(['edit articles', 'delete articles'])` 
it returns `true`, because the user has all these direct permissions. 
When we call
`$user->hasAnyDirectPermission('edit articles')`, it returns `true` because the user has one of the provided permissions.


You can examine all of these permissions:

```php
// Direct permissions
$user->getDirectPermissions() // Or $user->permissions;

// Permissions inherited from the user's groups
$user->getPermissionsViaGroups();

// All permissions which apply on the user (inherited and direct)
$user->getAllPermissions();
```

All these responses are collections of `Spatie\Permission\Models\Permission` objects.



If we follow the previous example, the first response will be a collection with the `delete article` permission and 
the second will be a collection with the `edit article` permission and the third will contain both.

If we follow the previous example, the first response will be a collection with the `delete article` permission and 
the second will be a collection with the `edit article` permission and the third will contain both.



### NOTE about using permission names in policies

When calling `authorize()` for a policy method, if you have a permission named the same as one of those policy methods, your permission "name" will take precedence and not fire the policy. For this reason it may be wise to avoid naming your permissions the same as the methods in your policy. While you can define your own method names, you can read more about the defaults Laravel offers in Laravel's documentation at https://laravel.com/docs/authorization#writing-policies

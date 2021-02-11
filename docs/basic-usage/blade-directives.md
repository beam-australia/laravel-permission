---
title: Blade directives
weight: 4
---

## Permissions
This package doesn't add any **permission**-specific Blade directives. 
Instead, use Laravel's native `@can` directive to check if a user has a certain permission.

```php
@can('edit articles')
  //
@endcan
```
or
```php
@if(auth()->user()->can('edit articles') && $some_other_condition)
  //
@endif
```

You can use `@can`, `@cannot`, `@canany`, and `@guest` to test for permission-related access.


## Groups 
As discussed in the Best Practices section of the docs, **it is strongly recommended to always use permission directives**, instead of group directives.

Additionally, if your reason for testing against Groups is for a Super-Admin, see the *Defining A Super-Admin* section of the docs.

If you actually need to test for Groups, this package offers some Blade directives to verify whether the currently logged in user has all or any of a given list of groups. 

Optionally you can pass in the `guard` that the check will be performed on as a second argument.

#### Blade and Groups
Check for a specific group:
```php
@group('writer')
    I am a writer!
@else
    I am not a writer...
@endgroup
```
is the same as
```php
@hasgroup('writer')
    I am a writer!
@else
    I am not a writer...
@endhasgroup
```

Check for any group in a list:
```php
@hasanygroup($collectionOfGroups)
    I have one or more of these groups!
@else
    I have none of these groups...
@endhasanygroup
// or
@hasanygroup('writer|admin')
    I am either a writer or an admin or both!
@else
    I have none of these groups...
@endhasanygroup
```
Check for all groups:

```php
@hasallgroups($collectionOfGroups)
    I have all of these groups!
@else
    I do not have all of these groups...
@endhasallgroups
// or
@hasallgroups('writer|admin')
    I am both a writer and an admin!
@else
    I do not have all of these groups...
@endhasallgroups
```

Alternatively, `@unlessgroup` gives the reverse for checking a singular group, like this:

```php
@unlessgroup('does not have this group')
    I do not have the group
@else
    I do have the group
@endunlessgroup
```


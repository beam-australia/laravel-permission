---
title: Timestamps
weight: 8
---

### Excluding Timestamps from JSON

If you want to exclude timestamps from JSON output of group/permission pivots, you can extend the Group and Permission models into your own App namespace and mark the pivot as hidden:

```php
    protected $hidden = ['pivot'];
 ```

### Adding Timestamps to Pivots

If you want to add timestamps to your pivot tables, you can do it with a few steps:
 - update the tables by calling `$table->timestamps();` in a migration
 - extend the Permission and Group models and add `->withTimestamps();` to the BelongsToMany relationshps for `groups()` and `permissions()`
 - update your User models (wherever you use the HasGroups or HasPermissions traits) by adding `->withTimestamps();` to the BelongsToMany relationshps for `groups()` and `permissions()`


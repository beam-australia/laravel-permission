---
title: Using a middleware
weight: 7
---

## Default Middleware

For checking against a single permission (see Best Practices) using `can`, you can use the built-in Laravel middleware provided by `\Illuminate\Auth\Middleware\Authorize::class` like this:

```php
Route::group(['middleware' => ['can:publish articles']], function () {
    //
});
```

## Package Middleware

This package comes with `GroupMiddleware`, `PermissionMiddleware` and `GroupOrPermissionMiddleware` middleware. You can add them inside your `app/Http/Kernel.php` file.

```php
protected $routeMiddleware = [
    // ...
    'group' => \Spatie\Permission\Middlewares\GroupMiddleware::class,
    'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
    'group_or_permission' => \Spatie\Permission\Middlewares\GroupOrPermissionMiddleware::class,
];
```

Then you can protect your routes using middleware rules:

```php
Route::group(['middleware' => ['group:super-admin']], function () {
    //
});

Route::group(['middleware' => ['permission:publish articles']], function () {
    //
});

Route::group(['middleware' => ['group:super-admin','permission:publish articles']], function () {
    //
});

Route::group(['middleware' => ['group_or_permission:super-admin|edit articles']], function () {
    //
});

Route::group(['middleware' => ['group_or_permission:publish articles']], function () {
    //
});
```

Alternatively, you can separate multiple groups or permission with a `|` (pipe) character:

```php
Route::group(['middleware' => ['group:super-admin|writer']], function () {
    //
});

Route::group(['middleware' => ['permission:publish articles|edit articles']], function () {
    //
});

Route::group(['middleware' => ['group_or_permission:super-admin|edit articles']], function () {
    //
});
```

You can protect your controllers similarly, by setting desired middleware in the constructor:

```php
public function __construct()
{
    $this->middleware(['group:super-admin','permission:publish articles|edit articles']);
}
```

```php
public function __construct()
{
    $this->middleware(['group_or_permission:super-admin|edit articles']);
}
```

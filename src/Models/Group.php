<?php

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Contracts\Group as GroupContract;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\GroupAlreadyExists;
use Spatie\Permission\Exceptions\GroupDoesNotExist;
use Spatie\Permission\Guard;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\RefreshesPermissionCache;

class Group extends Model implements GroupContract
{
    use HasPermissions;
    use RefreshesPermissionCache;

    protected $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? config('auth.defaults.guard');

        parent::__construct($attributes);
    }

    public function getTable()
    {
        return config('permission.table_names.groups', parent::getTable());
    }

    public static function create(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? Guard::getDefaultName(static::class);

        if (static::where('name', $attributes['name'])->where('guard_name', $attributes['guard_name'])->first()) {
            throw GroupAlreadyExists::create($attributes['name'], $attributes['guard_name']);
        }

        return static::query()->create($attributes);
    }

    /**
     * A group may be given various permissions.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.permission'),
            config('permission.table_names.group_has_permissions'),
            'group_id',
            'permission_id'
        );
    }

    /**
     * A group belongs to some users of the model associated with its guard.
     */
    public function users(): BelongsToMany
    {
        return $this->morphedByMany(
            getModelForGuard($this->attributes['guard_name']),
            'model',
            config('permission.table_names.model_has_groups'),
            'group_id',
            config('permission.column_names.model_morph_key')
        );
    }

    /**
     * Find a group by its name and guard name.
     *
     * @param string $name
     * @param string|null $guardName
     *
     * @return \Spatie\Permission\Contracts\Group|\Spatie\Permission\Models\Group
     *
     * @throws \Spatie\Permission\Exceptions\GroupDoesNotExist
     */
    public static function findByName(string $name, $guardName = null): GroupContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $group = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (! $group) {
            throw GroupDoesNotExist::named($name);
        }

        return $group;
    }

    public static function findById(int $id, $guardName = null): GroupContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $group = static::where('id', $id)->where('guard_name', $guardName)->first();

        if (! $group) {
            throw GroupDoesNotExist::withId($id);
        }

        return $group;
    }

    /**
     * Find or create group by its name (and optionally guardName).
     *
     * @param string $name
     * @param string|null $guardName
     *
     * @return \Spatie\Permission\Contracts\Group
     */
    public static function findOrCreate(string $name, $guardName = null): GroupContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $group = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (! $group) {
            return static::query()->create(['name' => $name, 'guard_name' => $guardName]);
        }

        return $group;
    }

    /**
     * Determine if the user may perform the given permission.
     *
     * @param string|Permission $permission
     *
     * @return bool
     *
     * @throws \Spatie\Permission\Exceptions\GuardDoesNotMatch
     */
    public function hasPermissionTo($permission): bool
    {
        if (config('permission.enable_wildcard_permission', false)) {
            return $this->hasWildcardPermission($permission, $this->getDefaultGuardName());
        }

        $permissionClass = $this->getPermissionClass();

        if (is_string($permission)) {
            $permission = $permissionClass->findByName($permission, $this->getDefaultGuardName());
        }

        if (is_int($permission)) {
            $permission = $permissionClass->findById($permission, $this->getDefaultGuardName());
        }

        if (! $this->getGuardNames()->contains($permission->guard_name)) {
            throw GuardDoesNotMatch::create($permission->guard_name, $this->getGuardNames());
        }

        return $this->permissions->contains('id', $permission->id);
    }
}

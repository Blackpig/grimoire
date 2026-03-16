<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Filament\Concerns;

use Illuminate\Support\Facades\Gate;

trait ChecksGrimoirePermissions
{
    protected static function checkPermission(string $key): bool
    {
        $permission = config("grimoire.permissions.{$key}");

        if (is_null($permission)) {
            return match ($key) {
                'view' => true,
                'edit' => false,
                default => false,
            };
        }

        if (is_callable($permission)) {
            return (bool) $permission(auth()->user());
        }

        if (is_string($permission)) {
            if (str_contains($permission, '@')) {
                [$class, $method] = explode('@', $permission, 2);

                return (bool) app($class)->$method(auth()->user());
            }

            return Gate::allows($permission);
        }

        return false;
    }
}

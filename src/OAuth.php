<?php

declare(strict_types=1);

namespace Rinvex\OAuth;

class OAuth
{
    /**
     * Get all of the defined scope IDs.
     *
     * @return array
     */
    public static function scopeIds()
    {
        return static::scopes()->pluck('id')->values()->all();
    }

    /**
     * Determine if the given scope has been defined.
     *
     * @param string $id
     *
     * @return bool
     */
    public static function hasScope($id)
    {
        return $id === '*' || array_key_exists($id, config('rinvex.oauth.scopes'));
    }

    /**
     * Get all of the scopes defined for the application.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function scopes()
    {
        return collect(config('rinvex.oauth.scopes'))->map(function ($description, $id) {
            return new Scope($id, $description);
        })->values();
    }

    /**
     * Get all of the scopes matching the given IDs.
     *
     * @param array $ids
     *
     * @return array
     */
    public static function scopesFor(array $ids)
    {
        return collect($ids)->map(function ($id) {
            if (isset(config('rinvex.oauth.scopes')[$id])) {
                return new Scope($id, config('rinvex.oauth.scopes')[$id]);
            }
        })->filter()->values()->all();
    }
}

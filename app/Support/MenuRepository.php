<?php

namespace App\Support;

class MenuRepository
{
    public static function admin(): array
    {
        return self::decorate(config('kpspams.menus.admin', []));
    }

    public static function operator(): array
    {
        return self::decorate(config('kpspams.menus.operator', []));
    }

    public static function user(): array
    {
        return self::decorate(config('kpspams.menus.user', []));
    }

    public static function all(): array
    {
        return array_merge(
            config('kpspams.menus.admin', []),
            config('kpspams.menus.operator', []),
            config('kpspams.menus.user', [])
        );
    }

    public static function find(string $slug): ?array
    {
        foreach (self::all() as $menu) {
            if ($menu['slug'] === $slug) {
                return self::decorate([$menu])[0];
            }
        }

        return null;
    }

    public static function operatorSlugs(): array
    {
        return array_column(config('kpspams.menus.operator', []), 'slug');
    }

    public static function userSlugs(): array
    {
        return array_column(config('kpspams.menus.user', []), 'slug');
    }

    private static function decorate(array $menus): array
    {
        return array_map(function (array $menu) {
            $menu['color'] = $menu['color'] ?? '#2563EB';
            $menu['icon'] = $menu['icon'] ?? 'apps';
            if ($menu['slug'] === 'user') {
                $menu['url'] = route('menu.user');
            } elseif ($menu['slug'] === 'area') {
                $menu['url'] = route('menu.area');
            } elseif ($menu['slug'] === 'golongan') {
                $menu['url'] = route('menu.golongan.index');
            } elseif ($menu['slug'] === 'catat-meter') {
                $menu['url'] = route('catat-meter.index');
            } elseif ($menu['slug'] === 'logs-perubahan') {
                $menu['url'] = route('menu.logs');
            } elseif ($menu['slug'] === 'data-meter') {
                $menu['url'] = route('menu.data-meter');
            } elseif ($menu['slug'] === 'data-pelanggan') {
                $menu['url'] = route('menu.customers.index');
            } else {
                $menu['url'] = route('menu.show', $menu['slug']);
            }

            return $menu;
        }, $menus);
    }
}

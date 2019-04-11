<?php

namespace App\Http\Middleware;

use Closure;
use Menu;
use Caffeinated\Menus\Builder;

class MenuMiddleware
{
    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure                  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Menu::make('sidebar', function(Builder $parent) {
            $navigations = \Config::get('app.navigations');
            foreach ($navigations as $navigation) {
                $this->addMenu($parent, $navigation);
            }
        });

        return $next($request);
    }

    protected function addMenu($parent, $navigation)
    {
        if (!$this->isMenuAllowed($navigation)) {
            return;
        }

        $menu = $parent->add(trans($navigation['label']), isset($navigation['route']) ? $navigation['route'] : '#');
        if (!empty($navigation['icon'])) {
            $menu->icon($navigation['icon']);
        }

        if (!empty($navigation['children'])) {
            foreach ($navigation['children'] as $child) {
                $this->addMenu($menu, $child);
            }
        }
    }

    protected function isMenuAllowed($navigation)
    {
        if (!empty($navigation['children'])) {
            $allowed = false;
            foreach ($navigation['children'] as $child) {
                $allowed = $this->isMenuAllowed($child) ? true : $allowed;
            }
        } else {
            $allowed   = true;
            $resource  = !empty($navigation['resource']) ? $navigation['resource'] : '';
            $privilege = !empty($navigation['privilege']) ? $navigation['privilege'] : '';

            if (!empty($resource) && !empty($privilege)) {
                $allowed = \Gate::allows('access', [$resource, $privilege]);
            }
        }

        return $allowed;
    }
}

<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

namespace davidxu\srbac\components;

use Yii;
use yii\base\InvalidConfigException;
use yii\caching\TagDependency;
use davidxu\srbac\models\Menu;
use yii\rbac\BaseManager;

/**
 * SrbacMenuHelper used to generate menu depend of user role.
 * Usage
 * 
 * ```
 * use davidxu\srbac\components\MenuHelper;
 * use davidxu\adminlte3\widgets\Menu;
 *
 * $callback = function ($menu) {
 *    $data = eval($menu['data']);
 *    return [
 *        'label' => $menu['name'],
 *        'url' => [$menu['route']],
 *        'options' => $data,
 *        'items' => $menu['children']
 *        ]
 *    ]
 * }
 *
 * $items = MenuHelper::getAssignedMenu(Yii::$app->user->id, null, $callback);
 *
 * echo Menu::widget([
 *    'items' => MenuHelper::getAssignedMenu(Yii::$app->user->id, null, $callback, true);
 * ]);
 * ```
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class MenuHelper
{
    /**
     * Use to get assigned menu of user.
     * @param mixed $userId
     * @param int|null $root
     * @param mixed|null $callback use to reformat output.
     * @param bool $refresh
     * callback should have format like
     *
     * ```
     * function ($menu) {
     *    return [
     *        'label' => $menu['name'],
     *        'url' => [$menu['route']],
     *        'options' => $data,
     *        'items' => $menu['children']
     *        ]
     *    ]
     * }
     * ```
     * @return array
     * @throws InvalidConfigException
     */
    public static function getAssignedMenu(mixed $userId, ?int $root = null, mixed $callback = null, bool $refresh = false): array
    {
        $config = Configs::instance();

        /* @var $manager BaseManager */
        $manager = Yii::$app->getAuthManager();
        $query = Menu::find()->asArray()->indexBy('id');
        $menus = $query->all();
        $key = [__METHOD__, $userId, $manager->defaultRoles];
        $cache = $config->cache;
        if ($refresh || $cache === null || ($assigned = $cache->get($key)) === false) {
            $routes = [];
            if ($userId !== null) {
                foreach ($manager->getPermissionsByUser($userId) as $name => $value) {
                    if ($name[0] === '/') {
                        if (str_ends_with($name, '/*')) {
                            $name = substr($name, 0, -1);
                        }
                        $routes[] = $name;
                    }
                }
            }
            foreach ($manager->defaultRoles as $role) {
                foreach ($manager->getPermissionsByRole($role) as $name => $value) {
                    if ($name[0] === '/') {
                        if (str_ends_with($name, '/*')) {
                            $name = substr($name, 0, -1);
                        }
                        $routes[] = $name;
                    }
                }
            }
            $routes = array_unique($routes);
            sort($routes);
            $assigned = [];
            $queryAssigned = Menu::find()->select(['id'])
                ->asArray();
            if (count($routes)) {
                $assigned = $queryAssigned->andWhere(['route' => $routes])->column();
            }
            $assigned = static::requiredParent($assigned, $menus);
            $cache?->set($key, $assigned, $config->cacheDuration, new TagDependency([
                'tags' => Configs::CACHE_TAG
            ]));
        }
        $key = [__METHOD__, $assigned, $root];
        if ($refresh || $callback !== null || $cache === null || (($result = $cache->get($key)) === false)) {
            $result = static::normalizeMenu($assigned, $menus, $callback, $root);
            if ($cache !== null && $callback === null) {
                $cache->set($key, $result, $config->cacheDuration, new TagDependency([
                    'tags' => Configs::CACHE_TAG
                ]));
            }
        }
        return $result;
    }

    /**
     * Ensure all item menu has parent.
     * @param array $assigned
     * @param array $menus
     * @return array
     */
    private static function requiredParent(array $assigned, array &$menus): array
    {
        $count = count($assigned);
        for ($i = 0; $i < $count; $i++) {
            $id = $assigned[$i];
            $parent_id = $menus[$id]['parent_id'] ?? null;
            if ($parent_id !== null && (int)$parent_id !== 0 && !in_array($parent_id, $assigned)) {
                $assigned[$count++] = $parent_id;
            }
        }
        return $assigned;
    }

    /**
     * Parse route
     * @param string $route
     * @return array|string
     */
    public static function parseRoute(string $route): array|string
    {
        if (!empty($route)) {
            $url = [];
            $r = explode('&', $route);
            $url[0] = $r[0];
            unset($r[0]);
            foreach ($r as $part) {
                $part = explode('=', $part);
                $url[$part[0]] = $part[1] ?? '';
            }
            return $url;
        }
        return '#';
    }

    /**
     * Normalize menu
     * @param array $assigned
     * @param array $menus
     * @param $callback
     * @param int|null $parent
     * @return array
     */
    private static function normalizeMenu(array &$assigned, array &$menus, $callback, int $parent = null): array
    {
        $result = [];
        $order = [];
        foreach ($assigned as $id) {
            $menu = $menus[$id];
            if ($menu['parent_id'] === $parent) {
                $menu['children'] = static::normalizeMenu($assigned, $menus, $callback, $id);
                if ($callback !== null) {
                    $item = call_user_func($callback, $menu);
                } else {
                    $item = [
                        'label' => $menu['name'],
                        'url' => static::parseRoute($menu['route']),
//                        'category' => $menu['cate_id']
                    ];
                    if (isset($menu['cate_id'])) {
                        $item['category'] = $menu['cate_id'];
                    }
                    if ($menu['children'] != []) {
                        $item['items'] = $menu['children'];
                    }
                }
                $result[] = $item;
                $order[] = $menu['order'];
            }
        }
        if ($result != []) {
            array_multisort($order, $result);
        }
        return $result;
    }

    /**
     * Get current menu category, if exists
     * @return int|null
     */
    public static function getCurrentCategory(): ?int
    {
        $route = Yii::$app->controller->getRoute();
        /** @var Menu $menu */
        $menu = Menu::find()->filterWhere(['like', 'route', $route])->one();
        return $menu && isset($menu->cate_id) ? $menu->cate_id : null;
    }
}

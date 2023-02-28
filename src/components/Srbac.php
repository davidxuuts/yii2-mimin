<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

namespace davidxu\srbac\components;

use Exception;
use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use davidxu\srbac\models\Item;
use yii\rbac\Item as RbacItem;

/**
 * @author Hafid Mukhlasin <hafidmukhlasin@gmail.com>
 * @author David Xu <david.xu.uts@163.com>
 * @since 1.0
 */
Class Srbac extends BaseObject
{

    private static array $_cachePathCheck = [];

	/**
	 * Method CheckRoute is used for checking if route right to access
	 *
	 * if ((Srbac::checkRoute($this->context->id.'/create'))){
	 *     echo Html::a('Create Foo', ['create'], ['class' => 'btn btn-success']);
	 * }
	 *
	 * @param string $route
	 * @param bool $strict
	 * @return bool
	 */
	public static function checkRoute(string $route, bool $strict = false): bool
    {
		$user = Yii::$app->user;
		$permission = (str_starts_with($route, '/')) ? $route : '/' . $route;
		$routePaths = explode('/', $permission);
		$currentPath = '';
		foreach ($routePaths as $path) {
            $currentPath = empty($currentPath) ? '/' : $currentPath . $path . '/';
            if(isset(static::$_cachePathCheck[$currentPath]) && static::$_cachePathCheck[$currentPath]) {
                return true;
            }
            $check = $user->can($currentPath) || $user->can($currentPath . '*');
            static::$_cachePathCheck[$currentPath] = $check;
            if($check){
                return true;
            }
        }
		if ($user->can($permission)) {
			return true;
		}

		if (!$strict) {
			$pos = (strrpos($permission, '/'));
			$parent = substr($permission, 0, $pos);
			$authItems = Item::find()->where('name LIKE :param AND type = :type')
                ->addParams([
                    ':param' => $parent . '/*',
                    ':type' => RbacItem::TYPE_PERMISSION
                ])->all();
			foreach ($authItems as $authItem) {
                /** @var Item $authItem */
				$permission = $authItem->name;
				if ($user->can($permission)) {
					return true;
				}
			}
		}

		$allowActions = Yii::$app->allowActions ?? null;

		foreach ($allowActions as $action) {
			$action = (str_starts_with($action, '/')) ? $action : '/' . $action;
			if ($action === '*' or $action === '*/*') {
				return true;
			} else if (str_ends_with($action, '*')) {
				$length = strlen($action) - 1;
				return (substr($action, 0, $length) == substr($route, 0, $length));
			} else {
				return ($action == $route);
			}
		}
		return false;
	}

    /**
     * Method FilterMenu is used for filtering right access menu
     *
     * $menuItems = [
     *     ['label' => 'Home', 'url' => ['/site/index']],
     *     ['label' => 'About', 'url' => ['/site/about']],
     * ];
     *
     * if (!Yii::$app->user->isGuest){
     *     $menuItems[] = ['label' => 'App', 'items' => [
     *         ['label' => 'Category', 'url' => ['/category/index']],
     *         ['label' => 'Product', 'url' => ['/product/index']],
     *       ]];
     * }
     *
     * $menuItems = Srbac::filterMenu($menuItems);
     *
     * echo Nav::widget([
     *     'options' => ['class' => 'navbar-nav navbar-right'],
     *     'items' => $menuItems,
     * ]);
     *
     * @param array|null $menus
     * @param bool $strict
     * @return array
     * @throws Exception
     */
	public static function filterMenu(?array $menus, bool $strict = false): array
    {
		$allowedRoutes = [];
		$hr = 0;
		foreach ($menus as $menu) {
			$items = ArrayHelper::getValue($menu, 'items');
			if (is_array($items)) {
				$allowedSubRoutes = [];
				foreach ($items as $item) {
					$urls = ArrayHelper::getValue($item, 'url');
					if (is_array($urls)) {
						$permission = $urls[0];
						$allowed = self::checkRoute($permission, $strict);
						if ($allowed) {
							$allowedSubRoutes[] = $item;
                        }
					} else {
						$allowedSubRoutes[] = $item;
						$hr++;
					}
				}
				if (count($allowedSubRoutes) > 0) {
					$menu['items'] = $allowedSubRoutes;
					$allowedRoutes[] = $menu;
                }
			} else {
				$urls = ArrayHelper::getValue($menu, 'url');
				if (is_array($urls)) {
					$permission = $urls[0];
					$allowed = self::checkRoute($permission, $strict);
					if ($allowed) {
						$allowedRoutes[] = $menu;
                    }
				} else {
					$allowedRoutes[] = $menu;
					$hr++;
				}
			}
		}
		if (count($allowedRoutes) == $hr) $allowedRoutes = [];
		return $allowedRoutes;
	}

	/**
	 * Method filterActionColumn is used for filtering template of Gridview Action Column
	 *
	 * echo GridView::widget([
	 *     'dataProvider' => $dataProvider,
	 *     'columns' => [
	 *         ...,
	 *         [
	 *            'class' => 'yii\grid\ActionColumn',
	 *            'template' => Srbac::filterActionColumn([
	 *                'update','delete','download'
	 *             ],$this->context->route),
	 *         ]
	 *     ]
	 * ]);
	 *
	 * The output is {update} {delete} {download}
	 *
	 * What's about 'delete' and 'drop'?
	 * If button name different with route name.
	 * But for best practice, it should same
	 *
	 * @param array|null $actions
	 * @param string $currentRoute
	 * @return string
	 */
	public static function filterActionColumn(?array $actions, string $currentRoute): string
    {
		$template = '';
		$pos = (strrpos($currentRoute, '/'));
		$parent = substr($currentRoute, 0, $pos);
		foreach ($actions as $key => $value) {
            $action = is_integer($key) ? $value : $key;
            $permission = $parent . '/' . $action;
            $button = "{" . $value . "} ";
			$allowed = self::checkRoute($permission, true);
			if ($allowed) {
				$template .= $button;
            } else {
				$allowed = self::checkRoute($parent . '/' . '*', true);
				if ($allowed) {
					$template .= $button;
				}
			}
		}
		return trim($template);
	}
}

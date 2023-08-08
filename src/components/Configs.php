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
use yii\base\InvalidConfigException;
use yii\caching\CacheInterface;
use yii\db\Connection;
use yii\caching\Cache;
use yii\helpers\ArrayHelper;
use yii\rbac\ManagerInterface;

/**
 * Configs
 * Used for configure some value. To set config you can use [[\yii\base\Application::$params]]
 *
 * ~~~
 * return [
 *
 *     'davidxu.srbac.configs' => [
 *         'db' => 'customDb',
 *         'menuTable' => '{{%admin_menu}}',
 *         'cache' => [
 *             'class' => 'yii\caching\FileCache',
 *         ],
 *     ]
 * ];
 * ~~~
 *
 * or use [[\Yii::$container]]
 *
 * ~~~
 * Yii::$container->set('davidxu\srbac\components\Configs',[
 *     'db' => 'customDb',
 *     'menuTable' => 'admin_menu',
 *     'cache' => [
 *         'class' => 'yii\caching\FileCache',
 *     ],
 * ]);
 * ~~~
 *
 * or set config for [[\yii\base\Application::$config]]
 * in 'config/main.php' or related config file
 *
 * ~~~
 * return [
 *    'id' => 'app-backend',
 *    'components' => [...],
 *    'container' => [
 *        'definitions' => [
 *            '\davidxu\srbac\Configs' => [
 *                'menuTable' => '{{%auth_menu}}',
 *                'menuCateTable' => '{{%menu_cate}}',
 *           ],
 *        ],
 *    ],
 * ];
 * ~~~
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @author David Xu <david.xu.uts@163.com>
 * @since 1.0
 */
class Configs extends BaseObject
{
    const CACHE_TAG = 'davidxu.srbac';

    public bool $useMenuCate = false;
    public bool $noCommonRouteGenerated = true;
    public array $exceptTypes = [];
    public array $exceptRoutes = [];

    /**
     * @var string Srbac config params
     */
    public static string $srbacParams = 'davidxu.srbac.configs';

    /**
     * @var ManagerInterface|string .
     */
    public string|ManagerInterface $authManager = 'authManager';

    /**
	 * @var Connection|string Database connection.
	 */
	public Connection|string $db = 'db';

	/**
	 * @var Cache|null|string Cache component.
	 */
	public string|null|Cache $cache = 'cache';

	/**
	 * @var int Cache duration. Default to a month.
	 */
	public int $cacheDuration = 2592000;

	/**
	 * @var string Menu table name.
	 */
	public string $menuTable = '{{%auth_menu}}';

    /**
     * @var string Menu table name.
     */
    public string $menuCateTable = '{{%auth_menu_cate}}';

    /**
     * @var string Route table name.
     */
    public string $routeTable = '{{%auth_route}}';

    /**
     * @var string User table name.
     */
    public string $userTable = '{{%user}}';

    /**
     * @var string AuthItem table name.
     */
    public string $authItemTable = '{{%auth_item}}';

    /**
     * @var string AuthItemChild table name.
     */
    public string $authItemChildTable = '{{%auth_item_child}}';

    /**
     * @var string AuthAssignment table name.
     */
    public string $authAssignmentTable = '{{%auth_assignment}}';

    /**
     * @var string AuthRule table name.
     */
    public string $authRuleTable = '{{%auth_rule}}';


    /** @var object|null */
	private static object|null $_instance = null;

	/**
	 * @inheritdoc
     * @throws InvalidConfigException
     */
	public function init()
	{
		if (!($this->db instanceof Connection)) {
			if (is_string($this->db) && !str_contains($this->db, '\\')) {
				$this->db = Yii::$app->get($this->db, false);
			} else {
				$this->db = Yii::createObject($this->db);
			}
		}
		if (!($this->cache instanceof Cache)) {
			if (is_string($this->cache) && !str_contains($this->cache, '\\')) {
				$this->cache = Yii::$app->get($this->cache, false);
			} else {
				$this->cache = Yii::createObject($this->cache);
			}
		}
		parent::init();
	}

    /**
     * Create instance of self
     *
     * @return object|null
     * @throws InvalidConfigException|Exception
     */
	public static function instance(): ?object
    {
		if (self::$_instance === null) {
			$type = ArrayHelper::getValue(Yii::$app->params, self::$srbacParams, []);
			if (is_array($type) && !isset($type['class'])) {
				$type['class'] = static::class;
			}
			return self::$_instance = Yii::createObject($type);
		}

		return self::$_instance;
	}

    /**
     * @return ManagerInterface|string
     * @throws Exception
     */
    public static function authManager(): ManagerInterface|string
    {
        return static::instance()->authManager;
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function userTable(): string
    {
        return static::instance()->userTable;
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function menuTable(): string
    {
        return static::instance()->menuTable;
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function menuCateTable(): string
    {
        return static::instance()->menuCateTable;
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function routeTable(): string
    {
        return static::instance()->routeTable;
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function authItemTable(): string
    {
        return static::instance()->authItemTable;
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function authRuleTable(): string
    {
        return static::instance()->authRuleTable;
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function authItemChildTable(): string
    {
        return static::instance()->authItemChildTable;
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function authAssignmentTable(): string
    {
        return static::instance()->authAssignmentTable;
    }

    /**
     * @return bool
     * @throws InvalidConfigException
     */
    public static function useMenuCate(): bool
    {
        return static::instance()->useMenuCate;
    }

    /**
     * @return bool
     * @throws InvalidConfigException
     */
    public static function noCommonRouteGenerated(): bool
    {
        return static::instance()->noCommonRouteGenerated;
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    public static function exceptTypes(): array
    {
        return static::instance()->exceptTypes;
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    public static function exceptRoutes(): array
    {
        return static::instance()->exceptRoutes;
    }

    /**
     * @return CacheInterface
     * @throws InvalidConfigException
     */
    public static function cache(): CacheInterface
    {
        return static::instance()->cache ?? Yii::$app->cache;
    }

    /**
     * @return integer
     * @throws InvalidConfigException
     */
    public static function cacheDuration(): int
    {
        return static::instance()->cacheDuration;
    }
}

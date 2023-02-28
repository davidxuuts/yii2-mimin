<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

namespace davidxu\srbac\components;

use davidxu\base\enums\StatusEnum;
use davidxu\srbac\models\MenuCate;
use Yii;
use yii\base\InvalidConfigException;
use yii\caching\TagDependency;

/**
 * Helper used to menuCate and menu.
 * Usage
 * 
 * ```
 * use davidxu\srbac\components\Helper;
 *
 * ```
 * $items = Helper::getMenuCate();
 * ```
 *
 * @author David XU <david.xu.uts@163.com>
 * @since 1.0
 */
class Helper
{
    /**
     * Use to get menu category.
     * @param string $appid
     * @return array
     * @throws InvalidConfigException
     */
    public static function getMenuCate(string $appid): array
    {
        if ($cache = Configs::cache()) {
            $dependency = new TagDependency(['tags' => Configs::CACHE_TAG]);
            return $cache->getOrSet([Yii::$app->id, __METHOD__, MenuCate::tableName()], function () use ($appid) {
                return MenuCate::find()->where([
                    'app_id' => $appid,
                    'status' => StatusEnum::ENABLED,
                ])->orderBy([
                    'order' => SORT_ASC,
                    'id' => SORT_ASC,
                ])->all();
            }, null, $dependency);
        } else {
            return MenuCate::find()->where([
                'app_id' => $appid,
                'status' => StatusEnum::ENABLED,
            ])->orderBy([
                'order' => SORT_ASC,
                'id' => SORT_ASC,
            ])->all();
        }
    }

    /**
     * Use to invalidate cache.
     * @throws InvalidConfigException
     */
    public static function invalidate(string|array $tags = Configs::CACHE_TAG): void
    {
        if (Configs::cache() !== null) {
            TagDependency::invalidate(Configs::cache(), $tags);
        }
    }
}

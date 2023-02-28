<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

namespace davidxu\srbac\models;

use davidxu\base\enums\StatusEnum;
use davidxu\srbac\components\Configs;
use Exception;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "auth_route".
 *
 * @property string $name Route name
 * @property string $alias Route alias
 * @property string $type Route type
 * @property int $status Route available status
 *
 * @property Menu[] $menus
 *
 * @property-read array $routes
 */
class Route extends ActiveRecord
{
    const CACHE_TAG         = 'davidxu.srbac.route';

    /**
     * @inheritdoc
     * @throws Exception
     */
	public static function tableName(): string
    {
        return Configs::instance()->routeTable;
	}

	/**
	 * @inheritdoc
	 */
	public function rules(): array
    {
		return [
            [['name', 'alias'], 'required'],
            [['name', 'alias', 'type'], 'string', 'max' => 64],
            [['name'], 'unique'],
            [['status'], 'default', 'value' => StatusEnum::ENABLED],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels(): array
    {
		return [
			'name' => Yii::t('srbac', 'Route'),
			'type' => Yii::t('srbac', 'Type'),
            'alias' => Yii::t('srbac', 'Alias'),
			'status' => Yii::t('base', 'Status'),
		];
	}

    /**
     * Gets query for [[Menus]].
     *
     * @return ActiveQuery
     */
    public function getMenus(): ActiveQuery
    {
        return $this->hasMany(Menu::class, ['route' => 'name']);
    }

    /**
     * Gets query for [[Route]]
     *
     * @param string $type Route type [[type]]
     * @return array
     */
    public function getRouteByType(string $type = ''): array
    {
        return self::find()->where([
            'status' => StatusEnum::ENABLED,
            'type' => $type,
            ])->all();
    }
}

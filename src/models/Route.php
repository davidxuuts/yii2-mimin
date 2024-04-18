<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

namespace davidxu\srbac\models;

use davidxu\base\enums\BooleanEnum;
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
 * @property string|null $type_name Route type name
 * @property int|null $is_auto Auto authorize
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
            [['name', 'alias', 'type', 'type_name'], 'string', 'max' => 64],
            [['name'], 'unique'],
            [['is_auto', 'status'], 'integer'],
            [['is_auto'], 'default', 'value' => BooleanEnum::NO],
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
            'type_name' => Yii::t('srbac', 'Type name'),
            'alias' => Yii::t('srbac', 'Alias'),
            'is_auto' => Yii::t('srbac', 'Auto authorize'),
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

<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

namespace davidxu\srbac\models;

use Exception;
use Yii;
use davidxu\srbac\components\Configs;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%menu}}".
 *
 * @property int $id Menu id(autoincrement)
 * @property int $cate_id Menu category
 * @property string $name Menu name
 * @property int|null $parent_id Menu parent
 * @property string $route Route name for this menu
 * @property int $order Menu order
 * @property resource|null $data Extra information for this menu
 *
 * @property Menu $parent Menu parent
 * @property Menu[] $children Menu children
 * @property MenuCate $cate Menu category
 * @property Route $routeName Menu route
 *
 * @author David Xu <david.xu.uts@gmail.com>
 * @since 1.0
 */
class Menu extends ActiveRecord
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public static function tableName(): string
    {
        return Configs::instance()->menuTable;
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['cate_id', 'order', 'parent_id'], 'integer'],
            [['name', 'route'], 'required'],
            [['data'], 'string'],
            [['name'], 'string', 'max' => 128],
            [['route'], 'string', 'max' => 64],
            [
                ['cate_id'], 'exist', 'skipOnError' => true,
                'targetClass' => MenuCate::class,
                'targetAttribute' => ['cate_id' => 'id']
            ],
            [
                ['route'], 'exist', 'skipOnError' => true,
                'targetClass' => Route::class,
                'targetAttribute' => ['route' => 'name']
            ],
            [
                ['parent_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Menu::class,
                'targetAttribute' => ['parent_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('srbac', 'ID'),
            'cate_id' => Yii::t('srbac', 'Menu category'),
            'name' => Yii::t('srbac', 'Name'),
            'parent_id' => Yii::t('srbac', 'Parent'),
            'route' => Yii::t('srbac', 'Route'),
            'order' => Yii::t('srbac', 'Order'),
            'data' => Yii::t('srbac', 'Data'),
        ];
    }

    /**
     * Gets query for [[Cate]].
     *
     * @return ActiveQuery
     */
    public function getCate(): ActiveQuery
    {
        return $this->hasOne(MenuCate::class, ['id' => 'cate_id']);
    }

    /**
     * Gets menu parent query for [[Parent]]
     * @return ActiveQuery
     */
    public function getParent(): ActiveQuery
    {
        return $this->hasOne(static::class, ['id' => 'parent_id']);
    }

    /**
     * Gets menu children query for [[Children]]
     * @return ActiveQuery
     */
    public function getChildren(): ActiveQuery
    {
        return $this->hasMany(static::class, ['parent_id' => 'id']);
    }

    /**
     * Gets query for [[Route]].
     *
     * @return ActiveQuery
     */
    public function getRouteName(): ActiveQuery
    {
        return $this->hasOne(Route::class, ['name' => 'route']);
    }
}

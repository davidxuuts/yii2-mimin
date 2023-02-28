<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

namespace davidxu\srbac\models;

use davidxu\base\enums\AppIdEnum;
use davidxu\base\enums\StatusEnum;
use davidxu\srbac\components\Configs;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%menu_cate}}".
 *
 * @property int $id ID
 * @property string $title Title
 * @property string $app_id App ID
 * @property string|null $icon Icon
 * @property int $order Order
 * @property int|null $status Status[-1:Deleted;0:Disabled;1:Enabled]
 * @property int $created_at Created at
 * @property int $updated_at Updated at
 *
 * @property Menu[] $menus
 */
class MenuCate extends ActiveRecord
{
    /**
     * {@inheritdoc}
     *
     * @return string
     * @throws InvalidConfigException
     */
    public static function tableName(): string
    {
        return Configs::instance()->menuCateTable;
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['title', 'app_id', 'order'], 'required'],
            [['order', 'status'], 'integer'],
            [['app_id'], 'string', 'max' => 20],
            [['app_id'], 'in', 'range' => AppIdEnum::getKeys()],
            [['status'], 'default', 'value' => StatusEnum::ENABLED],
            [['status'], 'in', 'range' => StatusEnum::getKeys()],
            [['title', 'icon'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'title' => Yii::t('srbac', 'Title'),
            'app_id' => 'App ID',
            'icon' => Yii::t('srbac', 'Icon'),
            'order' => Yii::t('srbac', 'Order'),
            'status' => Yii::t('srbac', 'Status'),
        ];
    }

    /**
     * Gets query for [[Menus]].
     *
     * @return ActiveQuery
     */
    public function getMenus(): ActiveQuery
    {
        return $this->hasMany(Menu::class, ['cate_id' => 'id']);
    }
}

<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

namespace davidxu\srbac\models;

use davidxu\srbac\components\Configs;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * This is the model class for table "auth_assignment".
 *
 * @property string $item_name Item name
 * @property string $user_id User ID
 * @property integer|null $created_at Created at
 *
 * @property Item $item
 */
class Assignment extends ActiveRecord
{
    /**
	 * @inheritdoc
     * @throws InvalidConfigException
     */
	public static function tableName(): string
    {
        return Configs::instance()->authAssignmentTable;
	}

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
        ];
    }

	/**
	 * @inheritdoc
	 */
	public function rules(): array
    {
		return [
			[['item_name', 'user_id'], 'required'],
            [['user_id'], 'integer'],
			[['item_name'], 'string', 'max' => 64],
            [['item_name', 'user_id'], 'unique', 'targetAttribute' => ['item_name', 'user_id']],
            [
                ['item_name'], 'exist', 'skipOnError' => true,
                'targetClass' => Item::class,
                'targetAttribute' => ['item_name' => 'name']
            ],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels(): array
    {
		return [
			'item_name' => Yii::t('srbac', 'Name'),
			'user_id' => Yii::t('srbac', 'User'),
			'created_at' => Yii::t('srbac', 'Created at'),
		];
	}

	/**
     * Gets query for [[Item]]
	 * @return ActiveQuery
	 */
	public function getItem(): ActiveQuery
    {
		return $this->hasOne(Item::class, ['name' => 'item_name']);
	}
}

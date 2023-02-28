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
use yii\rbac\Item as RbacItem;


/**
 * This is the model class for table "auth_item".
 *
 * @property string $name Name
 * @property int $type Type
 * @property string|null $description Description
 * @property string|null $rule_name Rule name
 * @property resource|null $data data
 * @property int|null $created_at Created at
 * @property int|null $updated_at Updated at
 *
 * @property Assignment[] $authAssignments
 * @property Item[] $children
 * @property Item[] $parents
 * @property Rule $ruleName
 */
class Item extends ActiveRecord
{
    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
	public static function tableName(): string
    {
        return Configs::instance()->authItemTable;
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
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    BaseActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
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
			[['name', 'type'], 'required'],
			[['type'], 'integer'],
            [['type'], 'in', 'range' => [RbacItem::TYPE_ROLE, RbacItem::TYPE_PERMISSION]],
			[['description', 'data'], 'string'],
			[['name', 'rule_name'], 'string', 'max' => 64],
            [['name'], 'unique'],
            [
                ['rule_name'], 'exist', 'skipOnError' => true,
                'targetClass' => Rule::class,
                'targetAttribute' => ['rule_name' => 'name']
            ],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels(): array
    {
		return [
            'name' => Yii::t('srbac', 'Name'),
            'type' => Yii::t('srbac', 'Type'),
            'description' => Yii::t('srbac', 'Description'),
            'rule_name' => Yii::t('srbac', 'Rule name'),
            'data' => Yii::t('srbac', 'Data'),
            'created_at' => Yii::t('srbac', 'Created at'),
            'updated_at' => Yii::t('srbac', 'Updated at'),
		];
	}

    /**
     * Gets query for [[AuthAssignments]]
     *
	 * @return ActiveQuery
	 */
	public function getAuthAssignments(): ActiveQuery
    {
		return $this->hasMany(Assignment::class, ['item_name' => 'name']);
	}

	/**
     * Gets query for [[RuleName]]
     *
	 * @return ActiveQuery
	 */
	public function getRuleName(): ActiveQuery
    {
		return $this->hasOne(Rule::class, ['name' => 'rule_name']);
	}

    /**
     * Gets query for [[Children]].
     *
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public function getChildren(): ActiveQuery
    {
        return $this->hasMany(Item::class, ['name' => 'child'])
            ->viaTable('{{%auth_item_child}}', ['parent' => 'name']);
    }

    /**
     * Gets query for [[Parents]].
     *
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public function getParents(): ActiveQuery
    {
        return $this->hasMany(Item::class, ['name' => 'parent'])
            ->viaTable('{{%auth_item_child}}', ['child' => 'name']);
    }
}

<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

namespace davidxu\srbac\models;

use davidxu\srbac\components\Configs;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user".
 *
 * @property integer $id ID
 * @property string $username Username
 * @property string $auth_key Auth key
 * @property string $password_hash Hashed password
 * @property string $password_reset_token Password reset token
 * @property string $email Email
 * @property integer $status Status
 * @property integer $created_at Created at
 * @property integer $updated_at Updated at
 */
class User extends ActiveRecord
{
	public ?string $new_password = null;
    public ?string $old_password = null;
    public ?string $repeat_password = null;

	/**
	 * @inheritdoc
     * @throws InvalidConfigException
     */
	public static function tableName()
	{
        return Configs::instance()->userTable;
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors(): array
    {
		return [
			TimestampBehavior::class,
		];
	}

	/**
	 * @inheritdoc
	 */
	public function rules(): array
    {
		return [
			[['username', 'email'], 'required'],
			[['username', 'email', 'password_hash'], 'string', 'max' => 255],
			[['username', 'email'], 'unique'],
			[['email'], 'email'],
			['status','integer'],
			[['old_password', 'new_password', 'repeat_password'], 'string', 'min' => 6],
			[['repeat_password'], 'compare', 'compareAttribute' => 'new_password'],
			[['old_password', 'new_password', 'repeat_password'], 'required', 'when' => function ($model) {
				return (!empty($model->new_password));
			}, 'whenClient' => "function (attribute, value) {
                return ($('#user-new_password').val().length>0);
            }"],
			//['username', 'filter', 'filter' => 'trim'],
			//['username', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This username has already been taken.'],
		];
	}

    /**
     * {@inheritDoc}
     */
	public function scenarios(): array
    {
		$scenarios = parent::scenarios();
		$scenarios['password'] = ['old_password', 'new_password', 'repeat_password'];
		return $scenarios;
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels(): array
    {
		return [
			'id' => 'ID',
			'username' => 'Username',
			'password_hash' => 'Password Hash',
			'email' => 'Email',
		];
	}

	/**
	 * Validates password
	 *
	 * @param string $password password to validate
	 * @return bool if password provided is valid for current user
	 */
	public function validatePassword(string $password): bool
    {
		return Yii::$app->security->validatePassword($password, $this->password_hash);
	}

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     * @throws Exception
     */
	public function setPassword(string $password)
	{
		$this->password_hash = Yii::$app->security->generatePasswordHash($password);
	}

	/**
     * Gets query for [[Roles]]
     *
	 * @return ActiveQuery
	 */
	public function getRoles(): ActiveQuery
    {
		return $this->hasMany(Assignment::class, [
			'user_id' => 'id',
		]);
	}
}

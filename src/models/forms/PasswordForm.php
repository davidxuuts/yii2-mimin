<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

namespace davidxu\srbac\models\forms;

use yii\web\User;
use yii\base\Model;
use Yii;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;

/**
 * Class PasswordForm
 * @package davidxu\srbac\models\forms
 *
 * @property string|null $old_password Original password
 * @property string|null $password New password
 * @property string|null $password_repeat Repeat new password
 *
 * @property-read ActiveRecord|ActiveRecordInterface|null|User|\davidxu\srbac\models\User
 */
class PasswordForm extends Model
{
    public ?string $old_password = null;

    public ?string $password = null;

    public ?string $password_repeat = null;

    private ActiveRecord|ActiveRecordInterface|null|User|\davidxu\srbac\models\User $_user = null;

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [['old_password', 'password', 'password_repeat'], 'filter', 'filter' => 'trim'],
            [['old_password', 'password', 'password_repeat'], 'required'],
            [['old_password', 'password', 'password_repeat'], 'string', 'min' => 6, 'max' => 15],
            [['password'], 'compare', 'message' => Yii::t('srbac', 'Password is not same as repeat password')],
            ['old_password', 'validatePassword'],
            ['password', 'notCompare'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function attributeLabels(): array
    {
        return [
            'old_password' => Yii::t('srbac', 'Original password'),
            'password' => Yii::t('srbac', 'Repeat password'),
            'password_repeat' => Yii::t('srbac', 'New password'),
        ];
    }

    /**
     * @param string $attribute
     */
    public function notCompare(string $attribute): void
    {
        if ($this->password === $this->old_password) {
            $this->addError($attribute, Yii::t('srbac', 'Same new password as original one'));
        }
    }

    /**
     * 验证原密码是否正确
     *
     * @param $attribute
     * @param $params
     */
    public function validatePassword($attribute, $params): void
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->old_password)) {
                $this->addError($attribute, Yii::t('srbac', 'Invalid original password'));
            }
        }
    }

    /**
     * 获取用户信息
     *
     * @return ActiveRecord|ActiveRecordInterface|User|\davidxu\srbac\models\User|null
     */
    protected function getUser(): ActiveRecord|ActiveRecordInterface|User|\davidxu\srbac\models\User|null
    {
        if ($this->_user === null) {
            $this->_user = Yii::$app->user->identity;
        }
        return $this->_user;
    }
}

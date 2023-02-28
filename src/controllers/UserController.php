<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

namespace davidxu\srbac\controllers;

use davidxu\base\enums\StatusEnum;
use davidxu\base\helpers\ActionHelper;
use davidxu\config\components\BaseController;
use davidxu\srbac\components\Helper;
use Yii;
use davidxu\srbac\models\forms\UserForm;
use yii\base\Exception;
use yii\base\ExitException;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecordInterface;
use yii\helpers\ArrayHelper;
use davidxu\srbac\models\Item;
use yii\rbac\Item as RbacItem;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends BaseController
{
    public string|ActiveRecordInterface|null $modelClass = '';

    public function init(): void
    {
        parent::init();
        if ($this->modelClass === '') {
            $this->modelClass = Yii::$app->getUser()->identityClass ? : Yii::$app->services->backendMemberService->modelClass;
        }
    }

    /**
     * @return array
     */
    public function actions(): array
    {
        return [];
    }

    /**
	 * Lists all User models.
     * @return string
     */
	public function actionIndex(): string
    {
        $query = $this->modelClass::find();
        // TODO auth rule needed
        if (!($seeAll = true)) {
            $query->where(['status' => StatusEnum::ENABLED]);
        }
        $key = trim(Yii::$app->request->get('key', ''));
        if ($key) {
            $query->andFilterWhere([
                'or',
                ['like', 'username', $key],
                ['like', 'realname', $key],
            ]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_ASC,
                    'updated_at' => SORT_DESC,
                ],
            ],
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
	}

    /**
     * @return mixed
     * @throws InvalidConfigException|ExitException
     */
    public function actionAjaxEdit(): mixed
    {
        $id = Yii::$app->request->get('id');
        $model = new UserForm(['id' => $id]);
        $authItems = ArrayHelper::map(
            Item::find()->select(['name'])->where([
                'type' => RbacItem::TYPE_ROLE,
            ])->asArray()->all(),
            'name', 'name');

        $model->loadData();
        ActionHelper::activeFormValidate($model);
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Helper::invalidate();
                return ActionHelper::message(Yii::t('srbac', 'Saved successfully'),
                    $this->redirect(Yii::$app->request->referrer));
            }
            return ActionHelper::message(ActionHelper::getError($model),
                    $this->redirect(Yii::$app->request->referrer), 'error');
        }

        return $this->renderAjax('ajax-edit', [
            'model' => $model,
            'authItems' => $authItems,
        ]);
    }

    /**
     * @throws InvalidConfigException
     */
    public function actionDestroy()
    {
        $id = Yii::$app->request->get('id');
        if (!($model = $this->modelClass::findOne($id))) {
            return ActionHelper::message(
                Yii::t('base', 'Data not found'),
                $this->redirect(Yii::$app->request->referrer),
                'error'
            );
        }
        if (isset($model->status)) {
            $model->status = StatusEnum::DELETE;
        }
        if ($model->save()) {
            Helper::invalidate();
            return ActionHelper::message(Yii::t('base', 'Deleted successfully'),
                $this->redirect(Yii::$app->request->referrer));
        }
        return ActionHelper::message(ActionHelper::getError($model),
                $this->redirect(Yii::$app->request->referrer), 'error');
    }

    /**
     * Action Destroy
     *
     * @param int $id
     * @return mixed
     * @throws InvalidConfigException
     */
    public function actionRestore(int $id): mixed
    {
        if (!($model = $this->modelClass::findOne($id))) {
            return ActionHelper::message(
                Yii::t('configtr', 'Data not found'),
                $this->redirect(Yii::$app->request->referrer),
                'error'
            );
        }

        if (isset($model->status)) {
            $model->status = StatusEnum::ENABLED;
        }
        if ($model->save()) {
            Helper::invalidate();
            return ActionHelper::message(
                Yii::t('srbac', 'Restored successfully'),
                $this->redirect(Yii::$app->request->referrer));
        }

        return ActionHelper::message(
            ActionHelper::getError($model),
            $this->redirect(Yii::$app->request->referrer),
            'error'
        );
    }
}

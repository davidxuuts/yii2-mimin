<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

namespace davidxu\srbac\controllers;

use davidxu\base\helpers\ActionHelper;
use davidxu\config\components\BaseController;
use davidxu\config\helpers\ResponseHelper;
use davidxu\srbac\components\Helper;
use Yii;
use davidxu\srbac\models\MenuCate;
use yii\base\ExitException;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use Throwable;

/**
 * MenuCateController implements the CRUD actions for MenuCate model.
 *
 * @author David Xu <david.xu.uts@163.com>
 * @since 1.0
 */
class MenuCateController extends BaseController
{
    public ActiveRecordInterface|string|null $modelClass = MenuCate::class;

    public function actions(): array
    {
        return [];
    }

    /**
     * Lists all MenuCate models.
     * @return mixed
     */
    public function actionIndex(): string
    {
        $query = MenuCate::find();
        $key = Yii::$app->request->get('key');
        if ($key) {
            $query->andFilterWhere(['like', 'title', $key]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'order' => SORT_ASC,
                    'id' => SORT_ASC,
                ],
            ],
        ]);
        return $this->render('index', [
                'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Create or edit [[MenuCate]] model
     * @return mixed
     * @throws ExitException|InvalidConfigException
     */
    public function actionAjaxEdit(): mixed
    {
        $id = Yii::$app->request->get('id', 0);
        /** @var MenuCate $model */
        $model = $this->findModel($id);
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
        return $this->renderAjax($this->action->id, [
            'model' => $model,
        ]);
    }

    /**
     * Set display order
     * @return array|mixed
     * @throws InvalidConfigException
     */
    public function actionSortOrder(): mixed
    {
        $id = Yii::$app->request->get('id');
        if (!($model = $this->modelClass::findOne($id))) {
            return ResponseHelper::json(404, Yii::t('base', 'Data not found'));
        }

        $model->attributes = ArrayHelper::filter(Yii::$app->request->get(), ['order', 'status']);
        if (!$model->save()) {
            return ResponseHelper::json(422, ActionHelper::getError($model));
        }
        Helper::invalidate();
        return ResponseHelper::json(200, Yii::t('base', 'Saved successfully'), $model->attributes);
    }

    /**
     * Delete a route
     * @throws StaleObjectException|Throwable
     */
    public function actionDelete()
    {
        $id = Yii::$app->request->get('id');
        /** @var Model|ActiveRecordInterface|ActiveRecord $model */
        $model = $this->findModel($id);
        if ($model->delete()) {
            Helper::invalidate();
            return ActionHelper::message(Yii::t('base', 'Deleted successfully'),
                $this->redirect(Yii::$app->request->referrer));
        }
        return ActionHelper::message(ActionHelper::getError($model),
                $this->redirect(Yii::$app->request->referrer), 'error');
    }
}

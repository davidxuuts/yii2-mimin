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
use davidxu\srbac\components\Configs;
use davidxu\srbac\components\Helper;
use davidxu\srbac\models\Menu;
use davidxu\srbac\models\MenuCate;
use davidxu\srbac\models\Route;
use Throwable;
use Yii;
use yii\base\ExitException;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;
use yii\db\StaleObjectException;
use yii\web\Response;
use davidxu\config\helpers\ArrayHelper;

/**
 * MenuController implements the CRUD actions for Menu model.
 */
class MenuController extends BaseController
{
    public string|ActiveRecordInterface|null $modelClass = Menu::class;

    public function actions(): array
    {
        return [];
    }

    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => $this->modelClass::find()
                ->orderBy(['order' => SORT_ASC]),
            'pagination' => false,
        ]);

        return $this->render($this->action->id, [
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * @throws ExitException|InvalidConfigException
     */
    public function actionAjaxEdit(): mixed
    {
        $id = Yii::$app->request->get('id', 0);
        /** @var Menu $model */
        $model = $this->findModel($id);
        if (Configs::useMenuCate()) {
            $model->scenario = $this->modelClass::SCENARIO_USE_MENU_CATE;
        }
        if ($model->isNewRecord && ($parent_id = Yii::$app->request->get('parent_id')) > 0) {
            $model->parent_id = $parent_id;
            /** @var Menu $modelParent */
            $modelParent = $this->findModel($parent_id);
            if ($model->scenario === $this->modelClass::SCENARIO_USE_MENU_CATE) {
                $model->cate_id = $modelParent->cate_id;
            }
        }
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
            'cateDropDownList' => $this->getCateDropDown(),
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
    /**
     * Get Available Routes
     * @param string $q
     * @param null $id
     * @return array|array[]
     */
    public function actionAvailableRoutes(string $q = '', $id = null): array
    {
        $query = trim($q);
        $result = [
            'results' => [
                'id' => '',
                'text' => '',
            ],
        ];
        if ($query) {
            $data = Route::find()
                ->select('name AS id, name AS text')
                ->filterWhere(['like', 'name', $query])
                ->limit(20)
                ->asArray()
                ->all();
            $result = ['results' => array_values($data)];
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $result;
    }

    /**
     * @return array|null
     */
    protected function getCateDropDown(): ?array
    {
        if (!Configs::useMenuCate()) {
            return null;
        }
        $list = MenuCate::find()
            ->select(['id', 'title'])
            ->orderBy('order asc, id asc')
            ->asArray()
            ->all();

        return ArrayHelper::map($list, 'id', 'title');
    }
}

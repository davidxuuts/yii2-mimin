<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

namespace davidxu\srbac\controllers;

use davidxu\base\helpers\ActionHelper;
use davidxu\config\components\BaseController;
use davidxu\srbac\components\Helper;
use davidxu\srbac\models\Rule;
use Exception;
use Yii;
use yii\base\ExitException;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecordInterface;
use yii\rbac\Rule as RbacRule;
use yii\web\Response;

/**
 * AuthRuleController implements the CRUD actions for AuthRule model.
 */
class RuleController extends BaseController
{
    public string|ActiveRecordInterface|null $modelClass = Rule::class;

    /**
     * @return array
     */
    public function actions(): array
    {
        return [];
    }

	/**
	 * Lists all AuthRule models.
	 * @return string
     */
	public function actionIndex(): string
    {
        $query = $this->modelClass::find();
        $key = trim(Yii::$app->request->get('key', ''));
        if ($key) {
            $query->andFilterWhere([
                'or',
                ['like', 'name', $key],
                ['like', 'data', $key],
            ]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'name' => SORT_ASC,
                    'updated_at' => SORT_DESC,
                    'created_at' => SORT_DESC,
                ],
            ],
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
	}

    /**
     * @return mixed|string|Response
     * @throws ExitException
     * @throws Exception
     */
    public function actionAjaxEdit(): mixed
    {
        $id = Yii::$app->request->get('id');
        /** @var Rule $model */
        $model = $this->findModel($id);
        $model->class_name = $model->getClassName();

        ActionHelper::activeFormValidate($model);
        if ($model->load(Yii::$app->request->post())) {
            $auth = Yii::$app->authManager;
            $rule = new $model->class_name;
            $rule->name = $rule->name ?? $model->name;
            if ($rule instanceof RbacRule) {
                $result = $model->isNewRecord ? $auth->add($rule) : $auth->update($rule->name, $rule);
                if ($result) {
                    Helper::invalidate();
                    ActionHelper::message(Yii::t('srbac', 'Saved successfully'),
                        $this->redirect(Yii::$app->request->referrer));
                }
                return ActionHelper::message(ActionHelper::getError($model),
                        $this->redirect(Yii::$app->request->referrer),
                        'error'
                    );
            }
            return ActionHelper::message(ActionHelper::getError($model),
                $this->redirect(Yii::$app->request->referrer),
                'error'
            );
        }

        return $this->renderAjax($this->action->id, [
            'model' => $model,
        ]);
    }

    /**
     * Delete a Rule
     * @return mixed
     * @throws InvalidConfigException
     */
    public function actionDelete(): mixed
    {
        /** @var Rule $model */
        $model = $this->findModel(Yii::$app->request->get('id'));
        $auth = Yii::$app->authManager;
        if ($auth->remove($auth->getRule($model->name))) {
            Helper::invalidate();
            return ActionHelper::message(Yii::t('srbac', 'Deleted successfully'),
                $this->redirect(Yii::$app->request->referrer));
        }
        return ActionHelper::message(Yii::t('srbac', 'Delete failed'),
                $this->redirect(Yii::$app->request->referrer),
                'error'
            );
    }
}

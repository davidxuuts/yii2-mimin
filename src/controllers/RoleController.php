<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

namespace davidxu\srbac\controllers;

use davidxu\base\helpers\ActionHelper;
use davidxu\config\components\BaseController;
use davidxu\base\enums\StatusEnum;
use davidxu\config\helpers\ArrayHelper;
use davidxu\srbac\components\Helper;
use davidxu\srbac\models\Rule;
use davidxu\srbac\models\Route;
use Exception;
use Throwable;
use Yii;
use davidxu\srbac\models\Item;
use yii\base\ExitException;
use yii\db\ActiveRecordInterface;
use yii\db\StaleObjectException;
use yii\rbac\Item as RbacItem;
use yii\data\ActiveDataProvider;
use yii\web\Response;

/**
 * AuthItemController implements the CRUD actions for AuthItem model.
 */
class RoleController extends BaseController
{
    public ActiveRecordInterface|string|null $modelClass = Item::class;

    /**
     * @return array
     */
    public function actions(): array
    {
        return [];
    }

	public function actionIndex(): string
    {
        $query = $this->modelClass::find()->where(['type' => RbacItem::TYPE_ROLE]);
        $key = trim(Yii::$app->request->get('key', ''));
        if ($key) {
            $query->andFilterWhere([
                'or',
                ['like', 'name', $key],
                ['like', 'description', $key],
                ['like', 'rule_name', $key],
            ]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'updated_at' => SORT_DESC,
                    'created_at' => SORT_DESC,
                    'name' => SORT_ASC,
                ],
            ],
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
	}

    /**
     * Displays a single AuthItem model.
     * @return string
     */
	public function actionAuthorize(): string
    {
        /** @var Item $model */
        $model = $this->findModel(Yii::$app->request->get('id'));
        $query = Route::find()
            ->groupBy(['type'])
            ->distinct()
            ->where(['status' => StatusEnum::ENABLED]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'type' => SORT_ASC,
                ],
            ],
        ]);

        $auth = Yii::$app->authManager;
        $permissions = $auth->getPermissionsByRole($model->name);
		return $this->render('authorize', [
			'model' => $model,
            'dataProvider' => $dataProvider,
            'permissions' => $permissions,
		]);
	}

    /**
     * @throws ExitException
     * @throws Exception
     */
    public function actionAjaxEdit()
    {
        $id = Yii::$app->request->get('id');
        /** @var Item $model */
        $model = $this->findModel($id);
        $model->type = RbacItem::TYPE_ROLE;

        $availableRules = ArrayHelper::map(
            Rule::find()->select(['name'])->asArray()->all(),
            'name', 'name'
        );

        ActionHelper::activeFormValidate($model);
        if ($model->load(Yii::$app->request->post())) {
            if (trim($model->rule_name) === '') {
                $model->rule_name = null;
            }
            $auth = Yii::$app->authManager;
            $role = $auth->createRole($model->name);
            $role->description = $model->description;
            $role->ruleName = $model->rule_name;
            $role->createdAt = $model->created_at;
            $role->updatedAt = $model->created_at;
            $success = $model->isNewRecord ? $auth->add($role) : $auth->update($model->name, $role);
            if ($success) {
                Helper::invalidate();
                return ActionHelper::message(Yii::t('srbac', 'Saved successfully'),
                    $this->redirect(Yii::$app->request->referrer));
            }
            return ActionHelper::message(ActionHelper::getError($model),
                    $this->redirect(Yii::$app->request->referrer),
                    'error'
                );
        }

        return $this->renderAjax($this->action->id, [
            'model' => $model,
            'availableRules' => $availableRules,
        ]);
    }

    /**
     * @return mixed
     * @throws StaleObjectException|Throwable
     */
	public function actionDelete(): mixed
    {
        /** @var Item $model */
        $model = $this->findModel(Yii::$app->request->get('id'));
		$auth = Yii::$app->authManager;
		$role = $auth->createRole($model->name);
        if ($auth->remove($role) && $model->delete()) {
            Helper::invalidate();
            return ActionHelper::message(Yii::t('srbac', 'Deleted successfully'),
                $this->redirect(Yii::$app->request->referrer));
        }
        return ActionHelper::message(ActionHelper::getError($model),
                $this->redirect(Yii::$app->request->referrer), 'error');
	}

    /**
     * @param string $role_name
     * @param string $permission_name
     * @return string[]
     * @throws Exception
     */
    public function actionPermission(string $role_name, string $permission_name): array
    {
		Yii::$app->response->format = Response::FORMAT_JSON;
		$auth = Yii::$app->authManager;
		$roleExist = $auth->getRole($role_name);
		$msg = 'no exec';
		if ($roleExist) {
			$role = $auth->createRole($role_name);
			$permissionExist = $auth->getPermission($permission_name);
            $permission = $auth->createPermission($permission_name);
            if (!$permissionExist) {
                $auth->add($permission);
            }

            if ($auth->hasChild($role, $permission)) {
				$auth->removeChild($role, $permission);
				//$auth->remove($permission);
				$msg = 'permission removed';
			} else {
				$auth->addChild($role, $permission);
				$msg = 'permission added';
			}
		}
        Helper::invalidate();
		return ['data' => $msg];
	}
}

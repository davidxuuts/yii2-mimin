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
use davidxu\srbac\models\MenuCate;
use Exception;
use ReflectionClass;
use Yii;
use davidxu\srbac\models\Route;
use yii\base\Controller;
use yii\base\ExitException;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\data\ActiveDataProvider;
use davidxu\srbac\components\Configs;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;
use yii\caching\TagDependency;

/**
 * RouteController implements the CRUD actions for Route model.
 */
class RouteController extends BaseController
{
    public string|ActiveRecordInterface|null $modelClass = Route::class;

    /**
     * @return array
     */
    public function actions(): array
    {
        $actions = parent::actions();
        unset(
            $actions['index'],
            $actions['edit'],
            $actions['ajax-edit'],
            $actions['destroy'],
            $actions['sort-order'],
        );
        return $actions;
    }

    /**
     * Lists all Route models.
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
                ['like', 'alias', $key],
                ['like', 'type', $key]
            ]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'type' => SORT_ASC,
                    'name' => SORT_ASC,
                ],
            ],
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
	}

    /**
     * Create or edit [[Route]] model
     * @return mixed
     * @throws ExitException|InvalidConfigException
     */
    public function actionAjaxEdit(): mixed
    {
        $id = Yii::$app->request->get('id', 0);
        /** @var Route $model */
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
     * Generate all routes.
     * @throws InvalidConfigException
     * @throws \yii\db\Exception
     */
	public function actionGenerate()
    {
        /** @var ActiveRecord $modelClass */
        $modelClass = $this->modelClass;
        $key = [__METHOD__, Yii::$app->id];
        $cache = Configs::instance()->cache;
        if ($cache === null || ($routes = $cache->get($key)) === false) {
            $routes = [];
            $this->getRouteRecursive(Yii::$app, $routes);
            $cache->set($key, $routes, Configs::instance()->cacheDuration, new TagDependency([
                'tags' => Route::CACHE_TAG
            ]));
        }
        $exists = $modelClass::find()->select(['name'])->column();
        $routes = array_diff($routes, $exists);
        $routes = array_unique($routes);
        sort($routes);
        $records = [];
        foreach ($routes as $route) {
            $pos = (strrpos($route, '/'));
            $type = substr($route, 1, $pos - 1);
            $alias = substr($route, $pos + 1, 64);
            if (Configs::noCommonRouteGenerated()) {
                if ($alias !== '*') {
                    $records[] = [
                        $route,
                        $type,
                        $alias,
                        StatusEnum::ENABLED
                    ];
                }
            } else {
                if ((!in_array($route, Configs::exceptRoutes())) || !in_array($type, Configs::exceptTypes())) {
                    $records[] = [
                        $route,
                        $type,
                        $alias,
                        StatusEnum::ENABLED
                    ];
                }
            }
//            $records[] = [
//                $route,
//                $type,
//                $alias,
//                StatusEnum::ENABLED
//            ];
        }
        Yii::$app->db->createCommand()
            ->batchInsert($modelClass::tableName(), ['name', 'type', 'alias', 'status'], $records)
            ->execute();
        Helper::invalidate();
        return ActionHelper::message(Yii::t('srbac', 'Routes generated successfully'),
            $this->redirect(Yii::$app->request->referrer));
	}

	/**
	 * Get route(s) recursive
     * @param Module $module
     * @param array $routes
     * @param array $excepts
     * @return void
     */
	private function getRouteRecursive(Module $module, array &$routes,
                                       array $excepts = ['yii\gii\Module', 'yii\debug\Module']): void
    {
        $token = "Get Route of '" . get_class($module) . "' with id '" . $module->uniqueId . "'";
		Yii::beginProfile($token, __METHOD__);
		try {
            if (!in_array(get_class($module), $excepts)) {
                foreach ($module->getModules() as $id => $child) {
                    if (($child = $module->getModule($id)) !== null) {
                        $this->getRouteRecursive($child, $routes, $excepts);
                    }
                }
                foreach ($module->controllerMap as $id => $type) {
                    $this->getControllerActions($type, $id, $module, $routes);
                }
                $namespace = trim($module->controllerNamespace, '\\') . '\\';
                $this->getControllerFiles($module, $namespace, '', $routes);
                $routes[] = ($module->uniqueId === '' ? '' : '/' . $module->uniqueId) . '/*';
            }
		} catch (Exception $exc) {
			Yii::error($exc->getMessage(), __METHOD__);
		}
		Yii::endProfile($token, __METHOD__);
	}

	/**
	 * Get list controller under module
     * @param $module
     * @param $namespace
     * @param $prefix
     * @param $result
     * @return void
     */
	private function getControllerFiles($module, $namespace, $prefix, &$result): void
    {
		$path = @Yii::getAlias('@' . str_replace('\\', '/', $namespace));
		$token = "Get controllers from '$path'";
		Yii::beginProfile($token, __METHOD__);
		try {
			if (!is_dir($path)) {
				return;
			}
			foreach (scandir($path) as $file) {
				if ($file == '.' || $file == '..') {
					continue;
				}
				if (is_dir($path . '/' . $file)) {
					$this->getControllerFiles($module, $namespace . $file . '\\', $prefix . $file . '/', $result);
				} elseif (strcmp(substr($file, -14), 'Controller.php') === 0) {
					$id = Inflector::camel2id(substr(basename($file), 0, -14));
					$className = $namespace . Inflector::id2camel($id) . 'Controller';
					if (!str_contains($className, '-')
                        && class_exists($className)
                        && is_subclass_of($className, Controller::class)
                    ) {
						$this->getControllerActions($className, $prefix . $id, $module, $result);
					}
				}
			}
		} catch (Exception $exc) {
			Yii::error($exc->getMessage(), __METHOD__);
		}
		Yii::endProfile($token, __METHOD__);
	}

	/**
	 * Get list action of controller
     * @param $type
     * @param $id
     * @param $module
     * @param $result
     * @return void
     */
	private function getControllerActions($type, $id, $module, &$result): void
    {
		$token = "Create controller with config=" . VarDumper::dumpAsString($type) . " and id='$id'";
		Yii::beginProfile($token, __METHOD__);
		try {
			/* @var $controller Controller */
			$controller = Yii::createObject($type, [$id, $module]);
			$this->getActionRoutes($controller, $result);
			$result[] = '/' . $controller->uniqueId . '/*';
		} catch (Exception $exc) {
			Yii::error($exc->getMessage(), __METHOD__);
		}
		Yii::endProfile($token, __METHOD__);
	}

	/**
	 * Get route of action
     * @param $controller
     * @param $result
     * @return void
     */
	private function getActionRoutes($controller, &$result): void
    {
		$token = "Get actions of controller '" . $controller->uniqueId . "'";
		Yii::beginProfile($token, __METHOD__);
		try {
			$prefix = '/' . $controller->uniqueId . '/';
			foreach ($controller->actions() as $id => $value) {
				$result[] = $prefix . $id;
			}
			$class = new ReflectionClass($controller);
			foreach ($class->getMethods() as $method) {
				$name = $method->getName();
				if ($method->isPublic() && !$method->isStatic() && str_starts_with($name, 'action') && $name !== 'actions') {
					$result[] = $prefix . Inflector::camel2id(substr($name, 6));
				}
			}
		} catch (Exception $exc) {
			Yii::error($exc->getMessage(), __METHOD__);
		}
		Yii::endProfile($token, __METHOD__);
	}

//    /**
//     * Set default rule of parameterize route.
//     * @return void
//     * @throws InvalidConfigException
//     * @throws Exception
//     */
//	protected function setDefaultRule(): void
//    {
//		if (Yii::$app->authManager->getRule(AuthRule::RULE_NAME) === null) {
//			Yii::$app->authManager->add(Yii::createObject([
//					'class' => AuthRule::class,
//					'name' => AuthRule::RULE_NAME]
//			));
//		}
//	}
}

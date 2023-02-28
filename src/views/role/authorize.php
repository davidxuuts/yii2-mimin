<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use davidxu\srbac\models\Route;
use yii\rbac\Permission;

/* @var $this yii\web\View */
/* @var $model Route */
/* @var $permissions Permission[] */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('srbac', 'Edit permissions');
$this->params['breadcrumbs'][] = ['label' => Yii::t('srbac', 'Roles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="srbac-auth-permission-index card card-outline card-secondary">
    <div class="card-header">
        <h4 class="card-title"><?= Html::encode($this->title . '-' . $model->name); ?> </h4>
    </div>
    <div class="card-body pt-3 pl-0 pr-0">
        <div class="container">
            <?php try {
                echo GridView::widget([
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table table-bordered table-hover'],
                    'columns' => [
                        'type',
                        [
                            'label' => Yii::t('srbac', 'Permissions'),
                            'format' => 'RAW',
                            'value' => function($model) use ($permissions) {
                                $str = '';
                                /** @var Route $model */
                                foreach ($model->getRouteByType($model->type) as $route) {
                                    $checked = false;
                                    /** @var Route $route */
                                    if(array_key_exists($route->name, $permissions)) {
                                        $checked = true;
                                    }
                                    $checkbox = Html::checkbox($route->type . '_' . $route->alias, $checked, [
                                        'id' => 'permission-checkbox-' . $route->primaryKey,
                                        'title' => $route->name,
                                        'class' => 'checkbox-permission form-check-input'
                                    ]);
                                    $label = Html::label(Yii::t('srbac', $route->alias),
                                        'permission-checkbox-' . $route->primaryKey, [
                                        'class' => 'form-check-label'
                                    ]);
                                    $str .= Html::tag('div', $checkbox . ' ' . $label, [
                                        'class' => 'form-check form-check-inline',
                                    ]);
                                }
                                return $str;
                            }
                        ],
                    ],
                ]);
            } catch (Exception|Throwable $e) {
                echo YII_ENV_PROD ? null : $e->getMessage();
            } ?>
        </div>
    </div>
</div>
<?php $baseUrl = Url::to(['permission']);
$js = /** @lang JavaScript */ <<< CHANGE_PERMISSION
$('.checkbox-permission').bind('click', function () {
    $.ajax({
        url: '{$baseUrl}',
        data: {
            role_name: '{$model->name}',
            permission_name: $(this).attr('title')
        }
    })
})

CHANGE_PERMISSION;
$this->registerJs($js);

<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

use davidxu\config\grid\ActionColumn;
use davidxu\config\helpers\Html;
use yii\widgets\Pjax;
use davidxu\srbac\models\Menu;
use davidxu\treegrid\TreeGrid;
use yii\web\View;
use yii\data\ActiveDataProvider;
use davidxu\base\enums\ModalSizeEnum;

/* @var $this View */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('srbac', 'Menus');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admin-menu-index card card-outline card-secondary">
    <div class="card-header">
        <h4 class="card-title"><?= Html::encode($this->title); ?> </h4>
        <div class="card-tools">
            <?= Html::a('<i class="fas fa-plus-circle"></i> ' . Yii::t('srbac', 'Create menu'),
                ['ajax-edit'],
                [
                    'class' => 'btn btn-xs btn-primary',
                    'title' => Yii::t('srbac', 'Edit'),
                    'arial-label' => Yii::t('srbac', 'Edit'),
                    'data-toggle' => 'modal',
                    'data-target' => '#modal',
                    'data-modal-class' => ModalSizeEnum::SIZE_LARGE,
                ]
            ) ?>
        </div>
    </div>
    <div class="card-body pt-3 pl-0 pr-0">
        <div class="container">
            <?php Pjax::begin(); ?>
            <?php try {
                echo TreeGrid::widget([
                    'dataProvider' => $dataProvider,
                    'keyColumnName' => 'id',
                    'parentColumnName' => 'parent_id',
                    'parentRootValue' => null, //first parentId value
                    'pluginOptions' => [
                        'initialState' => 'collapsed',
                    ],
                    'options' => ['class' => 'table table-hover pt-3'],
                    'columns' => [
                        [
                            'attribute' => 'name',
                            'format' => 'RAW',
                            'value' => function ($model) {
                                /** @var Menu $model */
                                $icon = $model->data ? '<i class="fas fa-' . $model->data . '"></i> ' : '';
                                $str = Html::tag('span', $icon . $model->name);
                                $str .= Html::a(' <i class="fas fa-plus-circle"></i>',
                                    ['ajax-edit', 'parent_id' => $model->id], [
                                        'title' => Yii::t('srbac', 'Edit'),
                                        'arial-label' => Yii::t('srbac', 'Edit'),
                                        'data-toggle' => 'modal',
                                        'data-target' => '#modal',
                                        'data-modal-class' => ModalSizeEnum::SIZE_LARGE,
                                    ]);
                                return $str;
                            }
                        ],
                        [
                            'attribute' => 'order',
                            'format' => 'raw',
                            'headerOptions' => ['class' => 'col-md-1'],
                            'value' => function ($model) {
                                /** @var Menu $model */
                                return Html::sort($model->order);
                            }
                        ],
                        [
                            'header' => Yii::t('app', 'Operation'),
                            'class' => ActionColumn::class,
                            'template' => '{ajax-edit} {delete}',
                        ],
                    ]
                ]);
            } catch (Exception|Throwable $e) {
                echo YII_ENV_PROD ? null : $e->getMessage();
            } ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>

<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

use davidxu\base\enums\ModalSizeEnum;
use yii\grid\GridView;
use yii\widgets\Pjax;
use davidxu\config\grid\ActionColumn;
use davidxu\srbac\models\MenuCate;
use davidxu\config\helpers\Html;
use yii\web\View;
use yii\data\ActiveDataProvider;

/* @var View $this*/
/* @var ActiveDataProvider $dataProvider */

$this->title = Yii::t('srbac', 'Menu category');
$this->params['breadcrumbs'][] = Yii::t('srbac', 'Admin');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admin-menu-cate-index card card-outline card-secondary">
    <div class="card-header">
        <h4 class="card-title"><?= Html::encode($this->title); ?> </h4>
        <div class="card-tools">
            <?= Html::a('<i class="fas fa-plus-circle"></i> ' . Yii::t('srbac', 'Create menu category'),
                ['ajax-edit'],
                [
                    'class' => 'btn btn-xs btn-primary',
                    'data-toggle' => 'modal',
                    'data-target' => '#modal',
                    'title' => Yii::t('srbac', 'Edit'),
                    'aria-label' => Yii::t('srbac', 'Edit'),
                    'data-modal-class' => ModalSizeEnum::SIZE_LARGE,
                ]) ?>
        </div>
    </div>
    <div class="card-body pt-3 pl-0 pr-0">
        <div class="container">
            <?php Pjax::begin(); ?>
            <?= $this->render('../common/_search', [
                'placeholder' => Yii::t('srbac', 'Search title')
            ]) ?>
            <?php try {
                echo GridView::widget([
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table table-striped table-bordered table-sm'],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'app_id',
                        'title',
                        [
                            'attribute' => 'icon',
                            'format' => 'RAW',
                            'value' => function($model) {
                                /** @var MenuCate $model */
                                $iconPrefix = count(explode(' ', $model->icon)) > 1 ? '' : 'fas fa-';
                                return '<i class="' . $iconPrefix . $model->icon . '"></i>';
                            }
                        ],
                        [
                            'format' => 'RAW',
                            'attribute' => 'order',
                            'headerOptions' => ['class' => 'col-md-1'],
                            'value' => function($model) {
                                /** @var MenuCate $model */
                                return Html::sort($model->order);
                            }
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'RAW',
                            'value' => function($model) {
                                /** @var MenuCate $model */
                                return Html::displayStatus($model->status);
                            }
                        ],
                        [
                            'class' => ActionColumn::class,
                            'header' => Yii::t('app', 'Operate'),
                            'template' => '{ajax-edit} {delete}',
                        ],
                    ],
                ]);
            } catch (Exception|Throwable $e) {
                echo YII_ENV_PROD ? null : $e->getMessage();
            } ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>

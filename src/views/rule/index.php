<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

use davidxu\srbac\models\Rule;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\web\View;
use davidxu\base\enums\ModalSizeEnum;
use yii\grid\SerialColumn;
use davidxu\config\grid\ActionColumn;

/**
 * @var $this View
 * @var $dataProvider ActiveDataProvider;
 */

$this->title = Yii::t('srbac', 'Rules');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="srbac-auth-rule-index card card-outline card-secondary">
    <div class="card-header">
        <h4 class="card-title"><?= Html::encode($this->title); ?> </h4>
        <div class="card-tools">
            <?= Html::a('<i class="fas fa-plus-circle"></i> '
                . Yii::t('srbac', 'Create rule'),
                ['ajax-edit'],
                [
                    'class' => 'btn btn-xs btn-primary',
                    'title' => Yii::t('srbac', 'Edit'),
                    'aria-label' => Yii::t('srbac', 'Edit'),
                    'data-toggle' => 'modal',
                    'data-target' => '#modal',
                    'data-modal-class' => ModalSizeEnum::SIZE_LARGE,
                ]
            ) ?>
        </div>
    </div>
    <div class="card-body pt-3 pl-0 pr-0">
        <div class="container">
            <?= $this->render('../common/_search', [
                'placeholder' => Yii::t('srbac', 'Search name')
            ]) ?>
            <?php try {
                echo GridView::widget([
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table table-bordered'],
                    'columns' => [
                        ['class' => SerialColumn::class],
                        'name',
                        [
                            'attribute' => 'class_name',
                            'value' => function($model) {
                                /** @var Rule $model */
                                return $model->data && unserialize($model->data) ? get_class(unserialize($model->data)) : null;
                            }
                        ],
                        'updated_at:date',
                        [
                            'class' => ActionColumn::class,
                            'header' => Yii::t('app', 'Operate'),
                            'template' => '{ajax-edit} {delete}',
                        ],
                    ],
                ]);
            } catch (Exception|\Throwable $e) {
                echo YII_ENV_PROD ? null : $e->getMessage();
            } ?>
        </div>
    </div>
</div>

<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\SerialColumn;
use davidxu\config\grid\ActionColumn;
use davidxu\base\enums\ModalSizeEnum;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('srbac', 'Routes');
$this->params['breadcrumbs'][] = Yii::t('srbac', 'Admin');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="srbac-route-index card card-outline card-secondary">
    <div class="card-header">
        <h4 class="card-title"><?= Html::encode($this->title); ?> </h4>
        <div class="card-tools">
            <?= Html::a('<i class="fas fa-plus-circle"></i> '
                . Yii::t('srbac', 'Create route'),
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
            <?= Html::a('<i class="fas fa-route"></i> '
                . Yii::t('srbac', 'Generate routes'),
                ['generate'],
                ['class' => 'btn btn-xs btn-secondary']
            ) ?>
        </div>
    </div>
    <div class="card-body pt-3 pl-0 pr-0">
        <div class="container">
            <?= $this->render('../common/_search', [
                'placeholder' => Yii::t('srbac', 'Search type/route/menu name')
            ]) ?>
            <?php try {
                echo GridView::widget([
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table table-bordered'],
                    'columns' => [
                        ['class' => SerialColumn::class],
                        'type',
                        'name',
                        'alias',
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
        </div>
    </div>
</div>

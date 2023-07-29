<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\View;
use yii\data\ActiveDataProvider;
use davidxu\base\enums\ModalSizeEnum;
use yii\grid\SerialColumn;
use davidxu\config\grid\ActionColumn;
use davidxu\base\enums\StatusEnum;

/**
 * @var $this View
 * @var $dataProvider ActiveDataProvider
 */

$this->title = Yii::t('srbac', 'Users');
$this->params['breadcrumbs'][] = Yii::t('srbac', 'Admin');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="srbac-user-index card card-outline card-secondary">
    <div class="card-header">
        <h4 class="card-title"><?= Html::encode($this->title); ?> </h4>
        <div class="card-tools">
            <?= Html::a('<i class="fas fa-plus-circle"></i> '
                . Yii::t('srbac', 'Create user'),
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
                'placeholder' => Yii::t('srbac', 'Search username/real name')
            ]) ?>
            <?php try {
                echo GridView::widget([
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table table-bordered'],
                    'columns' => [
                        ['class' => SerialColumn::class],
                        'username',
                        'realname',
                        [
                            'attribute' => 'roles',
                            'label' => Yii::t('srbac', 'Roles'),
                            'format' => 'raw',
                            'value' => function ($model) {
                                $roles = [];
                                $hasMemberServices = isset(Yii::$app->services) && isset(Yii::$app->services->backendMemberService);
                                if ($hasMemberServices) {
                                    $roles = Yii::$app->services->backendMemberService->getRoles($model->id);
                                }
//                                return implode(', ', $model->rolesName);
                                return implode(', ', $roles);
//                                return implode(', ', Yii::$app->services->backendMemberService->getRoles($model->id));
                            }
                        ],
                        [
                            'class' => ActionColumn::class,
                            'header' => Yii::t('app', 'Operate'),
                            'template' => '{restore} {ajax-edit} {destroy}',
                            'buttons' => [
                                'restore' => function($url) {
                                    return Html::a('<i class="fas fa-undo-alt"></i>', $url);
                                }
                            ],
                            'visibleButtons' => [
                                'restore' => function($model) {
                                    return $model->status === StatusEnum::DELETE;
                                },
                                'ajax-edit' => function($model) {
                                    return $model->status === StatusEnum::ENABLED;
                                },
                                'destroy' => function($model) {
                                    return $model->status === StatusEnum::ENABLED;
                                },
                            ],
                        ],
                    ],
                ]);
            } catch (Exception|Throwable $e) {
                echo YII_ENV_PROD ? null : $e->getMessage();
            } ?>
        </div>
    </div>
</div>

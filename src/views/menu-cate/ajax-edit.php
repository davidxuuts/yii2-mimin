<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

use yii\bootstrap4\ActiveForm;
use yii\helpers\Url;
use yii\web\View;
use yii\helpers\Html;
use davidxu\srbac\models\MenuCate;
use davidxu\base\enums\AppIdEnum;

/* @var View $this */
/* @var MenuCate $model */
/* @var ActiveForm $form */

$form = ActiveForm::begin([
    'id' => 'item-form',
    'enableAjaxValidation' => true,
    'options' => [
        'class' => 'form-horizontal',
    ],
    'validationUrl' => Url::to(['ajax-edit', 'id' => $model->id]),
    'fieldConfig' => [
        'options' => ['class' => 'form-group row'],
        'template' => "<div class='col-sm-2 text-right'>{label}</div>"
            . "<div class='col-sm-10'>{input}\n{hint}\n{error}</div>",
    ]
]);
?>

<div class="modal-header">
    <h4 class="modal-title"><?= Yii::t('app', 'Basic information') ?></h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>
<div class="modal-body">
    <?= $form->field($model, 'title')->textInput(['maxlength' => 128]) ?>
    <?= $form->field($model, 'app_id')->dropdownList(AppIdEnum::getManagement(), [
        'prompt' => Yii::t('srbac', 'Please select app id')
    ]) ?>
    <?= $form->field($model, 'order')->input('number') ?>
    <?= $form->field($model, 'icon')->textInput(['maxlength' => 50]) ?>
</div>
<div class="modal-footer">
    <?= Html::button(Yii::t('app', 'Close'), [
        'class' => 'btn btn-secondary',
        'data-dismiss' => 'modal'
    ]) ?>
    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end();

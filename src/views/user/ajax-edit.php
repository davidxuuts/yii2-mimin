<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

use yii\base\InvalidConfigException;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Url;
use yii\web\View;
use yii\helpers\Html;
use davidxu\srbac\models\forms\UserForm;
use kartik\select2\Select2;


/* @var $this View */
/* @var $model UserForm */
/* @var $form ActiveForm */
/* @var $authItems array */

$hint = $model->isNewUser
    ? Yii::t('srbac', 'If empty, random initial password will be generated (suggested)')
    : Yii::t('srbac', 'If change password is not need, please keep empty here (suggested)');
try {
$form = ActiveForm::begin([
    'id' => $model->formName(),
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
    <h4 class="modal-title"><?= Yii::t('srbac', 'Edit role') ?></h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>
<div class="modal-body">
    <?= $form->field($model, 'username')->textInput(['maxlength' => true, 'readonly' => !$model->isNewUser])
        ->hint(Yii::t('srbac', 'Can not modify username after account created')) ?>
    <?= $form->field($model, 'realname')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'password')->passwordInput()->hint($hint) ?>
    <?= $form->field($model, 'roles')->widget(Select2::class, [
        'data' => $authItems,
        'options' => [
            'placeholder' => Yii::t('srbac', '-- Select role --'),
            'multiple' => true,
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'dropdownParent' => '#modal',
            'tags' => true,
            'tokenSeparators' => [',', ' '],
        ],
    ])->label(Yii::t('srbac', 'Role')); ?>
</div>
<?php
} catch (InvalidConfigException $e) {
    echo YII_ENV_PROD ? null : $e->getMessage();
}
?>
    <div class="modal-footer">
        <?= Html::button(Yii::t('app', 'Close'), [
            'class' => 'btn btn-secondary',
            'data-dismiss' => 'modal'
        ]) ?>
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end();

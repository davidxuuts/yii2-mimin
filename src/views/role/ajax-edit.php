<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

use kartik\widgets\Select2;
use yii\base\InvalidConfigException;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Url;
use yii\web\View;
use yii\helpers\Html;
use davidxu\srbac\models\Item;

/* @var $this View */
/* @var $model Item */
/* @var $form ActiveForm */
/* @var $availableRules array */

try {
$form = ActiveForm::begin([
    'id' => $model->formName(),
    'enableAjaxValidation' => true,
    'options' => [
        'class' => 'form-horizontal',
    ],
    'validationUrl' => Url::to(['ajax-edit', 'id' => $model->primaryKey]),
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
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'rule_name')->widget(Select2::class, [
        'data' => $availableRules,
        'options' => [
            'placeholder' => Yii::t('srbac', '-- Please select --'),
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'dropdownParent' => '#modal',
        ],
    ])->label(Yii::t('srbac', 'Rule')); ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 2]) ?>
    <?= $form->field($model, 'data')->textarea(['rows' => 2]) ?>
</div>
<?php
} catch (InvalidConfigException|Exception $e) {
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

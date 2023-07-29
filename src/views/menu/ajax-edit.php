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
use davidxu\srbac\models\Menu;
use kartik\select2\Select2;
use yii\web\JsExpression;

/* @var $this View */
/* @var $model Menu */
/* @var $form ActiveForm */
/* @var $cateDropDownList ?array */

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
    <h4 class="modal-title"><?= Yii::t('srbac', 'Edit menu') ?></h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>
<div class="modal-body">
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?php if (\davidxu\srbac\components\Configs::useMenuCate() && !($model->parent_id > 0)) {
        echo $form->field($model, 'cate_id')->dropDownList(
            $cateDropDownList,
            ['prompt' => Yii::t('srbac', '-- Please select --'), 'type' => 'number']
        );
    } ?>
    <?php try {
        echo $form->field($model, 'route')->widget(Select2::class, [
            'options' => [
                'placeholder' => Yii::t('srbac', '-- Please select --'),
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => '#modal',
                'ajax' => [
                    'url' => Url::to(['available-routes']),
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function(res) { return res.text; }'),
            ],
        ])->label(Yii::t('srbac', 'Route'));
    } catch (Exception $e) {
        echo YII_ENV_PROD ? null : $e->getMessage();
    } ?>
    <?= $form->field($model, 'data')->textInput(['maxlength' => true])->label(Yii::t('srbac', 'Icon')) ?>
    <?= $form->field($model, 'order')->textInput(['type' => 'number']) ?>
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

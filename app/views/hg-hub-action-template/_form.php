<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgHubActionTemplate $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-hub-action-template-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'hg_version_id')->textInput() ?>

    <?= $form->field($model, 'hg_product_sensor_type_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'display_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'platform')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

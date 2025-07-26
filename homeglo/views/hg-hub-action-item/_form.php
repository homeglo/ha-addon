<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgHubActionItem $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-hub-action-item-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'entity')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'operation_name')->dropdownList(\app\models\HgHubActionItem::OPERATION_NAMES,['prompt'=>'']) ?>

    <?php
    $model->operation_value_json = json_encode($model->operation_value_json) ;
    echo $form->field($model, 'operation_value_json')->textInput() ?>

    <?= $form->field($model, 'operate_hg_device_light_group_id')->textInput(['disabled'=>true]) ?>

    <?= $form->field($model, 'hg_glo_id')->dropDownList(\yii\helpers\ArrayHelper::map(\app\models\HgGlo::getDefaultGlos(),'id','display_name'),['prompt'=>''])->label('Glo')?>

    <?= $form->field($model, 'override_bri_increment_percent')->textInput() ?>

    <?= $form->field($model, 'override_transition_duration_ms')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

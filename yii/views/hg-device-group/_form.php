<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgDeviceGroup $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-device-group-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'ha_device_id')->textInput(['disabled'=>true]) ?>

    <?= $form->field($model, 'room_invoke_order')->textInput() ?>

    <?= $form->field($model, 'display_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'hg_device_group_type_id')->
    dropDownList(\yii\helpers\ArrayHelper::map(\app\models\HgDeviceGroupType::find()->all(),'id','display_name'),['prompt'=>''])->label('Group Class'); ?>

    <?= $form->field($model, 'hg_glozone_id')->
    dropDownList(\yii\helpers\ArrayHelper::map($model->availableGlozones,'id','display_name'),['prompt'=>''])->label('Glozone'); ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgDeviceLight $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-device-light-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'display_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'serial')->textInput(['maxlength' => true]) ?>

    <?php
    if ($model->id)
        $q = \app\models\HgDeviceGroup::find()->where(['hg_hub_id'=>$model->hg_hub_id]);
    else {
        $glozones = $this->context->home_record->hgGlozones;
        $q = \app\models\HgDeviceGroup::find()->where(['hg_glozone_id'=>$glozones]);
    }

    echo $form->field($model, 'primary_hg_device_group_id')->
    dropDownList(\yii\helpers\ArrayHelper::map($q->all(),'id','display_name'),['prompt'=>''])->label('Room')?>

    <?= $form->field($model, 'hg_product_light_id')->
    dropDownList(\yii\helpers\ArrayHelper::map(\app\models\HgProductLight::find()->all(),'id',function($d) {
        return $d['display_name'].' ('.$d['model_id'].')';
    }),['prompt'=>''])->label('Light Product'); ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

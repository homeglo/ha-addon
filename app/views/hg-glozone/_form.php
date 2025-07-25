<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgGlozone $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-glozone-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'display_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bed_time_weekday_midnightmins')->widget(\kartik\time\TimePicker::classname(), [
        'pluginOptions'=>[
            'showMeridian'=>false,
            'minuteStep'=>1
        ]
    ]); ?>

    <?= $form->field($model, 'wake_time_weekday_midnightmins')->widget(\kartik\time\TimePicker::classname(), [
        'pluginOptions'=>[
            'showMeridian'=>false,
            'minuteStep'=>1
        ]
    ]); ?>



    <?= $form->field($model, 'bulb_startup_mode_hg_status_id')->dropDownList(\yii\helpers\ArrayHelper::map(\app\models\HgStatus::find()->where(['category_name'=>\app\models\HgStatus::CATEGORY_GLOZONE_STARTUPMODE])->all(),'id','display_name'),['prompt'=>'']);?>


    <?php
    if ($model->isNewRecord)
        echo $form->field($model, 'template_glozone_id')->dropDownList(\yii\helpers\ArrayHelper::map(\app\models\HgGlozone::find()->all(),'id','display_name'),['prompt'=>'']);?>


    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

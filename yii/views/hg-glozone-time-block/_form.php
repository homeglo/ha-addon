<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgGlozoneTimeBlock $model */
/** @var yii\widgets\ActiveForm $form */
/** @var \app\models\HgGlozone @hgGlozone */
?>

<div class="hg-glozone-time-block-form col-md-3">

    <?php $form = \yii\bootstrap4\ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'display_name')->textInput(['maxlength' => true]) ?>

    <?php // $form->field($model, 'default_hg_glo_id')->textInput() ?>

    <?php
    if ($model->isNewRecord) {
        $glos = $hgGlozone->hgGlos;
    } else {
        $glos = $model->hgGlozone->hgGlos;
    }

    echo $form->field($model, 'default_hg_glo_id')->
    dropDownList(\yii\helpers\ArrayHelper::map($glos,'id','display_name'),['prompt'=>''])->label('Glo')?>

    <?= $form->field($model, 'smartOn_switch_behavior')->radioList(\app\models\HgGlozoneTimeBlock::SMARTON_SWITCH_BEHAVIORS) ?>
    <?= $form->field($model, 'smartOn_motion_behavior')->radioList(\app\models\HgGlozoneTimeBlock::SMARTON_MOTION_BEHAVIORS) ?>
    <?= $form->field($model, 'smartTransition_behavior')->radioList(
            \app\models\HgGlozoneTimeBlock::SMARTTRANSITION_BEHAVIORS
    ) ?>
    <?= $form->field($model, 'smartTransition_duration_ms')->textInput()->hint('milliseconds e.g. 6000 = 6 seconds') ?>
    <?= $form->field($model, 'time_start_default_midnightmins')->textInput()->hint('
        Variables:<br/>
        {{bed_time}} - <br/>
        {{wake_time}} - <br/>
        {{sunset}} - <br/>
        {{sunrise}} - <br/>
    '); ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php \yii\bootstrap4\ActiveForm::end(); ?>

</div>

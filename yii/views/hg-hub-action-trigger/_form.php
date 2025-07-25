<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgHubActionTrigger $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-hub-action-trigger-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'display_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'event_name')->textInput(['maxlength' => true]) ?>

    <?php
    $model->event_data = json_encode($model->event_data);
    echo $form->field($model, 'event_data')->textInput() ?>

    <?= $form->field($model, 'hg_glozone_start_time_block_id')->textInput() ?>

    <?= $form->field($model, 'hg_glozone_end_time_block_id')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

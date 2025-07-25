<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgDeviceSensorVariableSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-device-sensor-variable-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'created_at') ?>

    <?= $form->field($model, 'updated_at') ?>

    <?= $form->field($model, 'display_name') ?>

    <?= $form->field($model, 'hg_device_sensor_id') ?>

    <?php // echo $form->field($model, 'variable_name') ?>

    <?php // echo $form->field($model, 'value') ?>

    <?php // echo $form->field($model, 'hg_status_id') ?>

    <?php // echo $form->field($model, 'description') ?>

    <?php // echo $form->field($model, 'json_data') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

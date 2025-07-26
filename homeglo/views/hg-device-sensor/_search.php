<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgDeviceSensorSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-device-sensor-search">

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

    <?= $form->field($model, 'hg_hub_id') ?>

    <?= $form->field($model, 'hue_id') ?>

    <?php // echo $form->field($model, 'display_name') ?>

    <?php // echo $form->field($model, 'hg_device_group_id') ?>

    <?php // echo $form->field($model, 'hg_product_sensor_id') ?>

    <?php // echo $form->field($model, 'hg_device_sensor_placement_id') ?>

    <?php // echo $form->field($model, 'switch_dimmer_increment_percent') ?>

    <?php // echo $form->field($model, 'button_1_long_press_hg_hub_action_id') ?>

    <?php // echo $form->field($model, 'button_2_long_press_hg_hub_action_id') ?>

    <?php // echo $form->field($model, 'button_3_long_press_hg_hub_action_id') ?>

    <?php // echo $form->field($model, 'button_4_long_press_hg_hub_action_id') ?>

    <?php // echo $form->field($model, 'button_1_short_press_hg_hub_action_id') ?>

    <?php // echo $form->field($model, 'button_2_short_press_hg_hub_action_id') ?>

    <?php // echo $form->field($model, 'button_3_short_press_hg_hub_action_id') ?>

    <?php // echo $form->field($model, 'button_4_short_press_hg_hub_action_id') ?>

    <?php // echo $form->field($model, 'presence_detected_hg_hub_action_id') ?>

    <?php // echo $form->field($model, 'presence_not_detected_hg_hub_action_id') ?>

    <?php // echo $form->field($model, 'metadata') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

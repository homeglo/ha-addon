<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgHubActionTriggerSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-hub-action-trigger-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'display_name') ?>

    <?= $form->field($model, 'source_name') ?>

    <?= $form->field($model, 'event_name') ?>

    <?php // echo $form->field($model, 'event_data') ?>

    <?php // echo $form->field($model, 'hg_hub_id') ?>

    <?php // echo $form->field($model, 'hg_device_sensor_id') ?>

    <?php // echo $form->field($model, 'hg_glozone_start_time_block_id') ?>

    <?php // echo $form->field($model, 'hg_glozone_end_time_block_id') ?>

    <?php // echo $form->field($model, 'hg_hub_action_template_id') ?>

    <?php // echo $form->field($model, 'hg_status_id') ?>

    <?php // echo $form->field($model, 'rank') ?>

    <?php // echo $form->field($model, 'metadata') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

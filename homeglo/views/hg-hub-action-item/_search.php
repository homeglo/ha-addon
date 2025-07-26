<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgHubActionItemSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-hub-action-item-search">

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

    <?= $form->field($model, 'hg_hub_action_trigger_id') ?>

    <?= $form->field($model, 'entity') ?>

    <?php // echo $form->field($model, 'operation_name') ?>

    <?php // echo $form->field($model, 'operation_value_json') ?>

    <?php // echo $form->field($model, 'operate_hg_device_light_group_id') ?>

    <?php // echo $form->field($model, 'hg_glo_id') ?>

    <?php // echo $form->field($model, 'display_name') ?>

    <?php // echo $form->field($model, 'override_hue_x') ?>

    <?php // echo $form->field($model, 'override_hue_y') ?>

    <?php // echo $form->field($model, 'override_bri_absolute') ?>

    <?php // echo $form->field($model, 'override_bri_increment_percent') ?>

    <?php // echo $form->field($model, 'override_transition_duration_ms') ?>

    <?php // echo $form->field($model, 'override_transition_at_time') ?>

    <?php // echo $form->field($model, 'metadata') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

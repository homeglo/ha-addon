<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgGlozoneTimeBlockSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-glozone-time-block-search">

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

    <?= $form->field($model, 'hg_glozone_id') ?>

    <?= $form->field($model, 'default_hg_glo_id') ?>

    <?php // echo $form->field($model, 'hg_status_id') ?>

    <?php // echo $form->field($model, 'base_hg_glozone_time_block_id') ?>

    <?php // echo $form->field($model, 'time_start_default_midnightmins') ?>

    <?php // echo $form->field($model, 'time_start_sun_midnightmins') ?>

    <?php // echo $form->field($model, 'time_end_sun_midnightmins') ?>

    <?php // echo $form->field($model, 'time_start_mon_midnightmins') ?>

    <?php // echo $form->field($model, 'time_end_mon_midnightmins') ?>

    <?php // echo $form->field($model, 'time_start_tue_midnightmins') ?>

    <?php // echo $form->field($model, 'time_end_tue_midnightmins') ?>

    <?php // echo $form->field($model, 'time_start_wed_midnightmins') ?>

    <?php // echo $form->field($model, 'time_end_wed_midnightmins') ?>

    <?php // echo $form->field($model, 'time_start_thu_midnightmins') ?>

    <?php // echo $form->field($model, 'time_end_thu_midnightmins') ?>

    <?php // echo $form->field($model, 'time_start_fri_midnightmins') ?>

    <?php // echo $form->field($model, 'time_end_fri_midnightmins') ?>

    <?php // echo $form->field($model, 'time_start_sat_midnightmins') ?>

    <?php // echo $form->field($model, 'time_end_sat_midnightmins') ?>

    <?php // echo $form->field($model, 'timezone') ?>

    <?php // echo $form->field($model, 'metadata') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

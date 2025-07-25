<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgGlozoneSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-glozone-search">

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

    <?= $form->field($model, 'hg_home_id') ?>

    <?= $form->field($model, 'name') ?>

    <?php // echo $form->field($model, 'display_name') ?>

    <?php // echo $form->field($model, 'bed_time_weekday_midnightmins') ?>

    <?php // echo $form->field($model, 'wake_time_weekday_midnightmins') ?>

    <?php // echo $form->field($model, 'bed_time_weekend_midnightmins') ?>

    <?php // echo $form->field($model, 'wake_time_weekend_midnightmins') ?>

    <?php // echo $form->field($model, 'metadata') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

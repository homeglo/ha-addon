<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgGlozoneSmartTransitionSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-glozone-smart-transition-search">

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

    <?= $form->field($model, 'hg_glozone_time_block_id') ?>

    <?= $form->field($model, 'hg_device_group_id') ?>

    <?php // echo $form->field($model, 'hg_status_id') ?>

    <?php // echo $form->field($model, 'rank') ?>

    <?php // echo $form->field($model, 'behavior_name') ?>

    <?php // echo $form->field($model, 'last_trigger_at') ?>

    <?php // echo $form->field($model, 'last_trigger_status') ?>

    <?php // echo $form->field($model, 'metadata') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

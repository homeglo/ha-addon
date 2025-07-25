<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgDeviceLightSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-device-light-search">

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

    <?php // echo $form->field($model, 'primary_hg_device_light_group_id') ?>

    <?php // echo $form->field($model, 'hg_product_light_id') ?>

    <?php // echo $form->field($model, 'hg_device_light_fixture') ?>

    <?php // echo $form->field($model, 'metadata') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgProductSensorSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-product-sensor-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'display_name') ?>

    <?= $form->field($model, 'manufacturer_name') ?>

    <?= $form->field($model, 'product_name') ?>

    <?= $form->field($model, 'type_name') ?>

    <?php // echo $form->field($model, 'archetype') ?>

    <?php // echo $form->field($model, 'model_id') ?>

    <?php // echo $form->field($model, 'description') ?>

    <?php // echo $form->field($model, 'rank') ?>

    <?php // echo $form->field($model, 'button_count') ?>

    <?php // echo $form->field($model, 'action_map_type') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

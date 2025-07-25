<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgProductLightSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-product-light-search">

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

    <?= $form->field($model, 'productid') ?>

    <?= $form->field($model, 'product_name') ?>

    <?php // echo $form->field($model, 'archetype') ?>

    <?php // echo $form->field($model, 'model_id') ?>

    <?php // echo $form->field($model, 'maxlumen') ?>

    <?php // echo $form->field($model, 'description') ?>

    <?php // echo $form->field($model, 'rank') ?>

    <?php // echo $form->field($model, 'version') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'range') ?>

    <?php // echo $form->field($model, 'capability_json') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

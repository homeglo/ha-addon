<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgProductLight $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-product-light-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'display_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'manufacturer_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'productid')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'product_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'archetype')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'model_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'maxlumen')->textInput() ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'rank')->textInput() ?>

    <?= $form->field($model, 'version')->textInput() ?>

    <?= $form->field($model, 'price')->textInput() ?>

    <?= $form->field($model, 'range')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'capability_json')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

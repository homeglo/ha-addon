<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgHub $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-hub-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'display_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'hue_email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'hue_random')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

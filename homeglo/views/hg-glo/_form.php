<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgGlo $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-glo-form">

    <?php $form = yii\bootstrap4\ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'display_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'base_hg_glo_id')->dropDownList(\yii\helpers\ArrayHelper::map(\app\models\HgGlo::getDefaultGlos(),'id','display_name'),['prompt'=>''])->label('Base Glo')?>

    <?= $form->field($model, 'ct')->textInput(['maxlength' => true])->hint('this will be used if both xy and CT are provided') ?>

    <?= $form->field($model, 'hue_x')->textInput(['maxlength' => true])->hint('e.g. 0.233') ?>

    <?= $form->field($model, 'hue_y')->textInput(['maxlength' => true])->hint('e.g. 0.444') ?>

    <?= $form->field($model, 'brightness')->textInput()->hint('1 - 255') ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php yii\bootstrap4\ActiveForm::end(); ?>

</div>

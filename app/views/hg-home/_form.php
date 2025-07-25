<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgHome $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-home-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'display_name')->textInput(['maxlength' => true])->hint('e.g. Ron Apt') ?>

    <?= $form->field($model, 'lat')->textInput() ?>

    <?= $form->field($model, 'lng')->textInput() ?>

    <?= $form->field($model, 'hg_status_id')
        ->dropDownList(\yii\helpers\ArrayHelper::map(\app\models\HgStatus::find()->where(['category_name'=>'home'])->all(),'id','display_name'),['prompt'=>''])
        ->label('Status')
        ->hint('Whether or not to run sunset/sunrise updaters, smart transitions'); ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgGlozoneSmartTransition $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-glozone-smart-transition-form">

    <?php $form = yii\bootstrap4\ActiveForm::begin(); ?>

    <?= $form->field($model, 'behavior_name')->radioList(
        \app\models\HgGlozoneTimeBlock::SMARTTRANSITION_BEHAVIORS
    ) ?>

    <?= $form->field($model, 'hg_status_id')->dropDownList(\yii\helpers\ArrayHelper::map(\app\models\HgStatus::find()->where(['category_name'=>\app\models\HgStatus::CATEGORY_SMART_TRANSITION])->all(),'id','display_name')); ?>


    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php yii\bootstrap4\ActiveForm::end(); ?>

</div>

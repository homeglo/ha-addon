<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgHubActionMap $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-hub-action-map-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'display_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'map_image_url')->textInput(['maxlength' => true]) ?>

    <?php //$model->preserve_hue_buttons = json_encode($model->preserve_hue_buttons);
    echo $form->field($model, 'preserve_hue_buttons')->checkboxList(\app\models\HgDeviceSensor::HUE_4BUTTON_SWITCH_IDS) ?>

    <?= $form->field($model, 'hg_product_sensor_map_type')->textInput(['maxlength' => true]) ?>

    <?php //$form->field($model, 'hg_status_id')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

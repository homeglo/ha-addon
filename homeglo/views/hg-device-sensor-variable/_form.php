<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgDeviceSensorVariable $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-device-sensor-variable-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'display_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sensor_type_name')->dropDownList(\app\models\HgProductSensor::SENSOR_TYPE_MAP) ?>

    <?= $form->field($model, 'variable_name')->textInput() ?>

    <?= $form->field($model, 'value')->textInput() ?>

    <?= $form->field($model, 'override_hg_product_sensor_id')
        ->dropDownList(\yii\helpers\ArrayHelper::map(\app\models\HgProductSensor::find()->all(),'id',function ($data) {return $data['display_name'].' ('.$data['model_id'].')';}),['prompt'=>'Default for a specific model...']); ?>


    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

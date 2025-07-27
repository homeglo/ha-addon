<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgDeviceSensor $model */
/** @var yii\widgets\ActiveForm $form */
?>

        <?php $form = \yii\bootstrap4\ActiveForm::begin(); ?>

        <?= $form->field($model, 'display_name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'hg_device_group_id')->
        dropDownList(\yii\helpers\ArrayHelper::map(\app\models\HgDeviceGroup::findByHgHubId($model->hg_hub_id),'id','display_name'),['prompt'=>'None'])->label('Room')?>

        <?php
                $opts = $model->getHgDeviceGroupMultiRoomOptions();
                echo $form->field($model, 'hg_device_group_multiroom_ids')->
        checkboxList(\yii\helpers\ArrayHelper::map($opts,'id','display_name'),['prompt'=>'None'])
                    ->label('Multi Room Control')
                    ->hint($opts ? '' : 'No multi rooms available in this glozone OR this hub!');
                ?>


        <?= $form->field($model, 'hg_hub_action_map_id')->
        dropDownList(\yii\helpers\ArrayHelper::map(\app\models\HgHubActionMap::find()->where(['hg_product_sensor_map_type'=>$model->hgProductSensor->action_map_type])->all(),'id','display_name'),['prompt'=>'','disabled'=>true])->label('Action Map'); ?>


        <?= $form->field($model, 'hg_product_sensor_id')->
        dropDownList(\yii\helpers\ArrayHelper::map(\app\models\HgProductSensor::find()->all(),'id','display_name'),['prompt'=>'None'])->label('Sensor Product')?>

        <?= $form->field($model, 'hg_device_sensor_placement_id')->
        dropDownList(\yii\helpers\ArrayHelper::map(\app\models\HgDeviceSensorPlacement::find()->all(),'id','display_name'),['prompt'=>'None'])->label('Sensor Placement')?>


        <?php //$form->field($model, 'switch_dimmer_increment_percent')->textInput() ?>
        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        </div>

        <?php \yii\bootstrap4\ActiveForm::end(); ?>



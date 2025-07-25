<?php
/** @var app\models\HgDeviceSensor[] $models */
/** @var app\models\InitSwitchForm $initMotionForm */
?>


    <div class="col-md-5 card card-body bg-light col-xs">
        <h1>Mass Motion Updater</h1>
        <?php

        $mapArray = \app\models\HgHubActionMap::find()->where(['IS','base_hg_hub_action_map_id',NULL])->andWhere(['hg_product_sensor_map_type'=>\app\models\HgHubActionMap::TYPE_HUE_MOTION_SENSOR])->all();
        $form = \yii\bootstrap4\ActiveForm::begin([
            'id' => 'init-motion-form',
            'action'=>['/hg-device-sensor/init-motion'],
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n{input}\n{error}",
                'labelOptions' => ['class' => 'col-xs-1 col-form-label mr-lg-3'],
                'inputOptions' => ['class' => 'col-xs-3 form-control'],
                'errorOptions' => ['class' => 'col-xs-7 invalid-feedback'],
            ],
        ]); ?>
        <?= $form->field($initMotionForm, 'template_hg_hub_action_map_id')->
        dropDownList(\yii\helpers\ArrayHelper::map($mapArray,'id',function($model) {return $model->display_name.' ('.$model->hg_product_sensor_map_type.')';}),['prompt'=>'Choose Map'])->label('Action Map'); ?>
        <?=$form->field($initMotionForm, 'hg_device_sensor_ids')
            ->checkboxList(\yii\helpers\ArrayHelper::map($models,'id',function (\app\models\HgDeviceSensor $model) {
                return $model->hgHubActionMap->display_name ?? $model->display_name;
            })); ?>

        <div class="form-group">
            <div>
                <?= \yii\helpers\Html::submitButton('Init Motion', [
                    'class' => 'btn btn-primary',
                    'onClick'=>'return confirm("DANGEROUS, will overwrite motion ENTIRELY!");']) ?>
            </div>
        </div>

        <?php \yii\bootstrap4\ActiveForm::end(); ?>
        <div class="col-md-12">
            <span id="map_image_motion"></span>
        </div>

    </div>

<?php



$str = '';
foreach ($mapArray as $hgHubActionMap) {
    $str .= "mapImgMotion[{$hgHubActionMap->id}] = '{$hgHubActionMap->map_image_url}';\n";
}

$js = <<<EOD


$("#initmotionform-template_hg_hub_action_map_id").change(function() {
    const mapImgMotion = [];
    $str
        
        $('input[type=checkbox]').prop('checked',false); 
      $("#map_image_motion").html('<img src="'+mapImgMotion[$( this ).val()]+'" width="500"/>');
    });
EOD;

$this->registerJs($js);
<?php
/** @var app\models\HgDeviceSensor[] $models */
/** @var app\models\InitSwitchForm $initSwitchForm */
?>


<div class="col-md-5 card card-body bg-light col-xs">
    <h1>Mass Switch Updater</h1>
    <?php

    $mapArray = \app\models\HgHubActionMap::find()
        ->where(['IS','base_hg_hub_action_map_id',NULL])
        ->andWhere(['hg_product_sensor_map_type'=>\app\models\HgHubActionMap::TYPE_HUE_DIMMER_SWITCH_4])
        ->all();

    $form = \yii\bootstrap4\ActiveForm::begin([
        'id' => 'init-switch-form',
        'action'=>['/hg-device-sensor/init-switches'],
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n{input}\n{error}",
            'labelOptions' => ['class' => 'col-xs-1 col-form-label mr-lg-3'],
            'inputOptions' => ['class' => 'col-xs-3 form-control'],
            'errorOptions' => ['class' => 'col-xs-7 invalid-feedback'],
        ],
    ]); ?>
    <?= $form->field($initSwitchForm, 'template_hg_hub_action_map_id')->
    dropDownList(\yii\helpers\ArrayHelper::map($mapArray,'id',function($model) {return $model->display_name.' ('.$model->hg_product_sensor_map_type.')';}),['prompt'=>'Choose Map'])->label('Action Map'); ?>
    <?= $form->field($initSwitchForm, 'preserve_buttons')
        ->checkboxList(\app\models\HgDeviceSensor::HUE_4BUTTON_SWITCH_IDS,[])
        ->hint('If no buttons are preserved, the entire switch will be overwritten')

    ;?>
    <?=$form->field($initSwitchForm, 'hg_device_sensor_ids')
        ->checkboxList(\yii\helpers\ArrayHelper::map($models,'id',function (\app\models\HgDeviceSensor $model) {
            return $model->hgHubActionMap->display_name ?? $model->display_name;
        })); ?>

    <div class="form-group">
        <div>
            <?= \yii\helpers\Html::submitButton('Init Switch', [
                'class' => 'btn btn-primary',
                'name' => 'login-button']); ?>
        </div>
    </div>

    <?php \yii\bootstrap4\ActiveForm::end(); ?>
    <div class="col-md-12">
        <span id="map_image"></span>
    </div>

</div>

<?php



$str = '';
$preserve_list = '';
foreach ($mapArray as $hgHubActionMap) {
    $str .= "mapImg[{$hgHubActionMap->id}] = '{$hgHubActionMap->map_image_url}';\n";
    if ($hgHubActionMap->preserve_hue_buttons)
        $preserve_list .= "preserveList[{$hgHubActionMap->id}] = ".json_encode($hgHubActionMap->preserve_hue_buttons).";\n";
}

$js = <<<EOD


$("#initswitchform-template_hg_hub_action_map_id").change(function() {
    const mapImg = [];
    const preserveList = [];
    $str
    $preserve_list
        
        $('input[type=checkbox]').prop('checked',false); 
      $("#map_image").html('<img src="'+mapImg[$( this ).val()]+'" width="500"/>');
      for (const element of preserveList[$( this ).val()]) {
        $(":checkbox[value="+element+"]").prop("checked","true");
      }
      //
    });
EOD;

$this->registerJs($js);
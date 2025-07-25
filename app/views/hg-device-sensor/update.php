<?php

use app\models\HgHubActionTrigger;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgDeviceSensor $model */

$this->title = 'Update Hg Device Sensor: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Hg Device Sensors', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="hg-device-sensor-update container ">
    <div class="row">
        <div class="hg-device-sensor-form col-md-6">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>


</div>



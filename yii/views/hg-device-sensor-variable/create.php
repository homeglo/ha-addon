<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgDeviceSensorVariable $model */

$this->title = 'Create Hg Device Sensor Variable';
$this->params['breadcrumbs'][] = ['label' => 'Hg Device Sensor Variables', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-device-sensor-variable-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

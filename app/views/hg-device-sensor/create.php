<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgDeviceSensor $model */

$this->title = 'Create Hg Device Sensor';
$this->params['breadcrumbs'][] = ['label' => 'Hg Device Sensors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-device-sensor-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgDeviceSensorVariable $model */

$this->title = 'Update Hg Device Sensor Variable: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Hg Device Sensor Variables', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="hg-device-sensor-variable-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

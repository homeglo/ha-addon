<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgProductSensor $model */

$this->title = 'Update Hg Product Sensor: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Hg Product Sensors', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="hg-product-sensor-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgProductSensor $model */

$this->title = 'Create Hg Product Sensor';
$this->params['breadcrumbs'][] = ['label' => 'Hg Product Sensors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-product-sensor-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

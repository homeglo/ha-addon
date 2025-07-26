<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgDeviceLight $model */

$this->title = 'Update Hg Device Light: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Hg Device Lights', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="hg-device-light-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

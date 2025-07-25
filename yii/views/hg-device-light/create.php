<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgDeviceLight $model */

$this->title = 'Create Hg Device Light';
$this->params['breadcrumbs'][] = ['label' => 'Hg Device Lights', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-device-light-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

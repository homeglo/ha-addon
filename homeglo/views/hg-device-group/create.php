<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgDeviceGroup $model */

$this->title = 'Create Hg Device Group';
$this->params['breadcrumbs'][] = ['label' => 'Hg Device Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-device-group-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

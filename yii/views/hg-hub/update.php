<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgHub $model */

$this->title = 'Update Hg Hub: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Hg Hubs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="hg-hub-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

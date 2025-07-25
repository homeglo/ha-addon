<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgProductLight $model */

$this->title = 'Update Hg Product Light: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Hg Product Lights', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="hg-product-light-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

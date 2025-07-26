<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgProductLight $model */

$this->title = 'Create Hg Product Light';
$this->params['breadcrumbs'][] = ['label' => 'Hg Product Lights', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-product-light-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

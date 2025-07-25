<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgHome $model */

$this->title = 'Update Hg Home: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Hg Homes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="hg-home-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

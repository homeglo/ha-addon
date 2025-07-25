<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgHubActionMap $model */

$this->title = 'Update Hg Hub Action Map: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Hg Hub Action Maps', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="hg-hub-action-map-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

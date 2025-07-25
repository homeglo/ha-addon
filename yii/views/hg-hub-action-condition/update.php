<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgHubActionCondition $model */

$this->title = 'Update Hg Hub Action Condition: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Hg Hub Action Conditions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="hg-hub-action-condition-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

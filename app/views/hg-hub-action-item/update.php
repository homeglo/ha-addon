<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgHubActionItem $model */

$this->title = 'Update Hg Hub Action Item: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Hg Hub Action Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="hg-hub-action-item-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

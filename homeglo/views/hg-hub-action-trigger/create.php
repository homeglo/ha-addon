<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgHubActionTrigger $model */

$this->title = 'Create Hg Hub Action Trigger';
$this->params['breadcrumbs'][] = ['label' => 'Hg Hub Action Triggers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-hub-action-trigger-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

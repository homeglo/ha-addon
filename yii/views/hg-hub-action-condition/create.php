<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgHubActionCondition $model */

$this->title = 'Create Hg Hub Action Condition';
$this->params['breadcrumbs'][] = ['label' => 'Hg Hub Action Conditions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-hub-action-condition-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

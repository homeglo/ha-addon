<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgHubActionMap $model */

$this->title = 'Create Hg Hub Action Map';
$this->params['breadcrumbs'][] = ['label' => 'Hg Hub Action Maps', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-hub-action-map-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

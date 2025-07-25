<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgHub $model */

$this->title = 'Create Hg Hub';
$this->params['breadcrumbs'][] = ['label' => 'Hg Hubs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-hub-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

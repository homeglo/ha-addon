<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgGlozoneTimeBlock $model */

$this->title = 'Update Hg Glozone Time Block: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Hg Glozone Time Blocks', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="hg-glozone-time-block-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgGlo $model */

$this->title = 'Update Hg Glo: ' . $model->display_name;
$this->params['breadcrumbs'][] = ['label' => 'Hg Glos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->display_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="hg-glo-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

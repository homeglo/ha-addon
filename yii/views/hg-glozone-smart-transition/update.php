<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgGlozoneSmartTransition $model */

$this->title = 'Update Hg Glozone Smart Transition: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Hg Glozone Smart Transitions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="hg-glozone-smart-transition-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

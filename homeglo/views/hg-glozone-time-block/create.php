<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgGlozoneTimeBlock $model */
/** @var app\models\HgGlozone $hgGlozone */

$this->title = 'Create Glo Time';
$this->params['breadcrumbs'][] = ['label' => 'Hg Glozone Time Blocks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-glozone-time-block-create">

    <?= $this->render('_form', [
        'model' => $model,
        'hgGlozone'=>$hgGlozone
    ]) ?>

</div>

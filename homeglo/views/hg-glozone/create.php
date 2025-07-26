<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgGlozone $model */

$this->title = 'Create Hg Glozone';
$this->params['breadcrumbs'][] = ['label' => 'Hg Glozones', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-glozone-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

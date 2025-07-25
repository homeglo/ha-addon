<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgHome $model */

$this->title = 'Create Hg Home';
$this->params['breadcrumbs'][] = ['label' => 'Hg Homes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-home-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

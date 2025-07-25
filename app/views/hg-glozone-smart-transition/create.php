<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgGlozoneSmartTransition $model */

$this->title = 'Create Hg Glozone Smart Transition';
$this->params['breadcrumbs'][] = ['label' => 'Hg Glozone Smart Transitions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-glozone-smart-transition-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

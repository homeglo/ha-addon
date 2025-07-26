<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgGlo $model */

$this->title = 'Create Hg Glo';
$this->params['breadcrumbs'][] = ['label' => 'Hg Glos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-glo-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

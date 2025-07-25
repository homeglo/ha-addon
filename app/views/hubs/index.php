<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Hubs';
$this->params['breadcrumbs'][] = ['label'=>$this->context->home_record['name'],'url'=>['/site/enter-home','id'=>$this->context->home_record['id']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= \yii\grid\GridView::widget([
        'dataProvider' => $provider,
        'columns' => [
            'id',
            'display_name',
            'access_token',
            'bearer_token',
            'token_expires_at:datetime',
        ],
    ]) ?>

</div>

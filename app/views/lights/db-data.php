<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Lights';
$this->params['breadcrumbs'][] = ['label'=>$this->context->home_record['display_name'],'url'=>['/site/enter-home','id'=>$this->context->home_record['id']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>
    <p><?=Html::a('Add Light','/lights/add-light',['class'=>'btn btn-info']);?></p>

    <?= \yii\grid\GridView::widget([
        'dataProvider' => $provider,
        'columns' => [
            'hgHub.display_name',
            'hue_id',
            'display_name',
            'primaryHgDeviceLightGroup.display_name',
            'hgProductLight.display_name',
            'hgDeviceLightFixture.display_name',
            [
                'class' => 'yii\grid\ActionColumn',
            ]
        ],
    ]) ?>

</div>

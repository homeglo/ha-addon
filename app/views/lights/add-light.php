<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Add Light';
$this->params['breadcrumbs'][] = ['label'=>$this->context->home_record['display_name'],'url'=>['/site/enter-home','id'=>$this->context->home_record['id']]];
$this->params['breadcrumbs'][] = ['label'=>'Lights','url'=>['/lights']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        <?php
        $scan_active = false;
        foreach ($last_scan as $hub_id => $scan_time) {
            if ($scan_time == 'active')
                $scan_active = true;

            echo "Hub: $hub_id -> Last scan: $scan_time <br/>";

        } ?>
    </p>
    <?php if ($scan_active) { ?>
        <p><?=Html::a('Refresh new lights','/lights/add-light',['class'=>'btn btn-info']);?></p>
    <?php } else { ?>
        <p><?=Html::a('Search for lights...','/lights/scan-lights',['class'=>'btn btn-primary']);?></p>
    <?php } ?>





    <?= \yii\grid\GridView::widget([
        'dataProvider' => $provider,
        'columns' => [
            [
                'attribute'=>'sensor data',
                'value'=>function ($data) {
                    return '<pre>'.json_encode($data,JSON_PRETTY_PRINT).'</pre>';
                },
                'format'=>'raw'
            ],
            [
                'attribute'=>'actions',
                'value'=>function ($data) {
                    return Html::a('Add light','/lights/scan-lights',['class'=>'btn btn-primary']);
                },
                'format'=>'raw'
            ]
        ],
    ]) ?>



</div>

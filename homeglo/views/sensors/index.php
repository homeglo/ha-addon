<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Sensors';
$this->params['breadcrumbs'][] = ['label'=>$this->context->home_record['display_name'],'url'=>['/site/enter-home','id'=>$this->context->home_record['id']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about" style="overflow-y:scroll">
    <p><?=Html::a('Add Sensor','/sensors/add-sensor',['class'=>'btn btn-info']);?></p>

    <?= \yii\grid\GridView::widget([
        'dataProvider' => $provider,
        'columns' => [
            'id',
            'name',
            'type',
            'productname',
            'modelid',
            'config.battery',
            [
                    'attribute'=>'ruleCount',
                    'value'=>function ($data) {
                        return Html::a($data['ruleCount'],['sensors/rules','id'=>$data['id'],'hub_id'=>$data['hub_id']],['target'=>'_blank']);
                    },
                'format'=>'raw'
            ],
            'hub',
            [
                'attribute'=>'data',
                'value'=>function ($data) {
                    unset($data['capabilities']);
                    return '<pre>'.json_encode($data,JSON_PRETTY_PRINT).'</pre>';
                },
                'format'=>'raw'
            ],
            [
                'attribute'=>'delete',
                'value'=>function ($data) {
                    return Html::a('Delete Sensor',['/sensors/delete-sensor','sensor_id'=>$data['id'],'hub_id'=>$data['hub_id']],['class'=>'btn btn-danger','onClick'=>'return confirm(\'Confirm delete sensor?\');']);
                },
                'format'=>'raw'
            ]
        ],
    ]) ?>


</div>

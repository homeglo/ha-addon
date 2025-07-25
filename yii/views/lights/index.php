<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Lights';
$this->params['breadcrumbs'][] = ['label'=>$this->context->home_record['display_name'],'url'=>['/site/enter-home','id'=>$this->context->home_record['id']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about" style="overflow-y:scroll">
    <p><?=Html::a('Add Light','/lights/add-light',['class'=>'btn btn-info']);?></p>

    <?= \yii\grid\GridView::widget([
        'dataProvider' => $provider,
        'columns' => [
            'id',
            'name',
            'modelid',
            'type',
            'config.archetype',
            'productname',
            'productid',
            'capabilities.control.maxlumen',
            'state.on',
            'state.bri',
            [
                    'attribute'=>'xy',
                'value'=>function($model) {
                    if (is_array($model['state']['xy']))
                        return implode(',',$model['state']['xy']);
                }
            ],
            'hub',
            [
                'attribute'=>'data',
                'value'=>function ($data) {
                    return '<pre>'.json_encode($data,JSON_PRETTY_PRINT).'</pre>';
                },
                'format'=>'raw'
            ],
            [
                'attribute'=>'delete',
                'value'=>function ($data) {
                    return Html::a('Delete Light',['/lights/delete-light','light_id'=>$data['id'],'hub_id'=>$data['hub_id']],['class'=>'btn btn-danger','onClick'=>'return confirm(\'Confirm delete light?\');']);
                },
                'format'=>'raw'
            ]
        ],
    ]) ?>

</div>

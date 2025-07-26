<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Rules';
$this->params['breadcrumbs'][] = ['label'=>$this->context->home_record['display_name'],'url'=>['/site/enter-home','id'=>$this->context->home_record['id']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= \yii\grid\GridView::widget([
        'dataProvider' => $provider,
        'columns' => [
            'id',
            'name',
            'modelid',
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
                    return Html::a('Delete rule',['/sensors/delete-rule','rule_id'=>$data['id'],'hub_id'=>$data['hub_id']],['class'=>'btn btn-danger','onClick'=>'return confirm(\'Confirm delete rule?\');']);
                },
                'format'=>'raw'
            ]
        ],
    ]) ?>



</div>

<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Scenes';
$this->params['breadcrumbs'][] = ['label'=>$this->context->home_record['display_name'],'url'=>['/site/enter-home','id'=>$this->context->home_record['id']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about" style="overflow-y:scroll">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= \yii\grid\GridView::widget([
        'dataProvider' => $provider,
        'columns' => [
            [
                'attribute'=>'id',
                'value'=>function ($data) {
                    return Html::a($data['id'],['/scenes/view','scene_id'=>$data['id'],'hub_id'=>$data['hub_id']],['target'=>'_blank']);
                },
                'format'=>'raw'
            ],
            'name',
            [
                    'attribute'=>'lights',
                    'value'=>function ($data) {
                        return implode('</br>',$data['lights']);
                    },
                'format'=>'raw'
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
                'attribute'=>'delete_scene',
                'header'=>'Delete',
                'value'=>function($data) {
                    return Html::a('Delete',['/scenes/delete','scene_id'=>$data['id'],'hub_id'=>$data['hub_id']],[
                        'class'=>'btn btn-danger',
                        'onClick'=>'return confirm("This will reset the scenes in the hub to the Glo defaults in the Glos section");']);
                },
                'format'=>'raw'
            ],
        ],

    ]) ?>

</div>

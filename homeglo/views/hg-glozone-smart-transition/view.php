<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\HgGlozoneSmartTransition $model */
/** @var \yii\data\ActiveDataProvider $hgGlozoneSmartTransitionExecuteProvider */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Glo Times', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->hgGlozoneTimeBlock->display_name.' (Glo: '.$model->hgGlozoneTimeBlock->defaultHgGlo->display_name.')', 'url' => ['/hg-glozone-time-block/view','id'=>$model->hgGlozoneTimeBlock->id]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="hg-glozone-smart-transition-view" style="overflow-y:scroll">


    <?= DetailView::widget([
        'model' => $model,
        'formatter'=>new \app\formatters\HgFormatter(),
        'attributes' => [
            'id',
            'created_at',
            'updated_at',
            'hgGlozoneTimeBlock.display_name',
            'hgDeviceGroup.display_name',
            'hgStatus.display_name',
            'behavior_name:hgGlozoneTimeBlockSmartBehaviorIcon',
            'last_trigger_at:datetime',
            'last_trigger_status',
            'metadata:jsonPrettyPrint',
        ],
    ]) ?>

    <?= \yii\grid\GridView::widget([
        'dataProvider' => $hgGlozoneSmartTransitionExecuteProvider,
        'columns' => [
            [
                'format'=>'raw',
                'value'=> function($data) {
                    if (!$data->isMostRecent)
                        return null;

                    return Html::a("Remove  <i class=\"fas fa-times\">", ['/hg-glozone-smart-transition/delete-execute-row','id'=>$data['id']], [
                        'title' => "Scan Lights",
                        'class' => 'btn btn-xs btn-danger',
                        'onClick'=>'return confirm(\'Removing this record may trigger this transition to execute on the next minute!\');'
                    ]);
                },
                'header'=>'Action',
            ],
            'id',
            'time_block_today_time:datetime',
            'updated_at:datetime',
            'attempt',
            'hgStatus.display_name',
            [
                'attribute'=>'data',
                'value'=>function ($data) {
                    return '<pre>'.json_encode($data->metadata,JSON_PRETTY_PRINT).'</pre>';
                },
                'format'=>'raw'
            ],
        ],
    ]) ?>

</div>

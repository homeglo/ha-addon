<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\HgGlozoneTimeBlock $model */
/** @var app\models\HgGlozoneSmartTransitionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = $model->display_name.' (Glo: '.$model->defaultHgGlo->display_name.')';
$this->params['breadcrumbs'][] = ['label' => 'Glo Times', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="hg-glozone-time-block-view">

    <?= DetailView::widget([
        'model' => $model,
        'formatter'=>new \app\formatters\HgFormatter(),
        'attributes' => [
            'id',
            'display_name',
            'smartTransition_behavior:hgGlozoneTimeBlockSmartBehaviorIcon',
            //'hgGlozone.display_name',
            'defaultHgGlo.display_name',
            [
                'attribute'=>'time_start_default_midnightmins',
                'header'=>'Time Start',
                'value'=>function($model) {
                    return date('h:i:s A',\app\components\HelperComponent::convertMidnightMinutesToEpochTime($model->calcStartMidnightmins,$model->timezone));
                }
            ],
            'timezone',
            //'metadata',
        ],
    ]) ?>
<h1>Room Transitions</h1>
    <?= \yii\grid\GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'formatter' => new \app\formatters\HgFormatter(),
        'columns' => [
            'id',
            'created_at:datetime',
            'hgGlozoneTimeBlock.display_name',
            'hgDeviceGroup.display_name',
            'hgStatus.display_name',
            //'rank',
            'behavior_name:hgGlozoneTimeBlockSmartBehaviorIcon',
            'last_trigger_at:datetime',
            'last_trigger_status',
            //'metadata',
            [
                'class' => \yii\grid\ActionColumn::className(),
                'urlCreator' => function ($action, \app\models\HgGlozoneSmartTransition $model, $key, $index, $column) {
                    return \yii\helpers\Url::toRoute(['/hg-glozone-smart-transition/'.$action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>

</div>

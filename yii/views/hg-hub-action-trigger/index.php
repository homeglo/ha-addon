<?php

use app\models\HgHubActionTrigger;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\HgHubActionTriggerSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Hg Hub Action Triggers';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-hub-action-trigger-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Hg Hub Action Trigger', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'display_name',
            'source_name',
            'event_name',
            //'event_data',
            //'hg_hub_id',
            //'hg_device_sensor_id',
            //'hgGlozoneStartTimeBlock.display_name',
            //'hgGlozoneEndTimeBlock.display_name',
            [
                'attribute'=>'time_start_default_midnightmins',
                'header'=>'Time Start',
                'value'=>function($model) {
                    return date('h:i:s A',\app\components\HelperComponent::convertMidnightMinutesToEpochTime($model->hgGlozoneStartTimeBlock->calcStartMidnightmins,$model->hgGlozoneStartTimeBlock->timezone));
                }
            ],
            //'hg_hub_action_template_id',
            //'hg_status_id',
            //'rank',
            //'metadata:ntext',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, HgHubActionTrigger $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

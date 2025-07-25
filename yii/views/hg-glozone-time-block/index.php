<?php

use app\components\HelperComponent;
use app\models\HgGlozoneTimeBlock;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\HgGlozoneTimeBlockSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Glo Times';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-glozone-time-block-index" style="overflow-y:scroll">

    <p>
        <?= Html::a('Create Glo Time', ['create','hg_glozone_id'=>$this->context->hg_glozone_id], ['class' => 'btn btn-success']) ?>

        <?php /* Html::a('Populate Factory Timeblocks', ['populate-time-blocks'], [
            'class' => 'btn btn-danger float-right',
            'data' => [
                'confirm' => 'This will WIPE OUT existing time blocks and transitions!',
            ],
        ]) */?>
    </p>


    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter'=>new \app\formatters\HgFormatter(),
        'rowOptions'=>function(HgGlozoneTimeBlock $model){
            if ($model->getIsCurrentlyActiveTimeBlockByTime()) {
                return ['class'=>'btn-info'];
            }
        },
        'columns' => [
            [
                'attribute'=>'calcStartMidnightmins',
                'header'=>'Time Start',
                'value'=>function($model) {
                    return date('h:i:s A',HelperComponent::convertMidnightMinutesToEpochTime($model->calcStartMidnightmins,$model->timezone));
                }
            ],
            [
                'attribute'=>'time_start_default_midnightmins',
                'header'=>'Time Start',
                'value'=>function($model) {
                    return $model->time_start_default_midnightmins;
                },
                'filter'=>false
            ],
            'defaultHgGlo.display_name',

            //'hg_status_id',
            //'base_hg_glozone_time_block_id:datetime',
            /*[
                'attribute'=>'computed_field',
                'header'=>'Time Start',
                'value'=>function($model) {
                    return $model->calcStartMidnightmins;
                }
            ],*/

            /*[
                'attribute'=>'time_end_default_midnightmins',
                'header'=>'Time End',
                'value'=>function($model) {
                    return date('h:i:s A',HelperComponent::convertMidnightMinutesToEpochTime($model->nextSequentialTimeBlock->calcStartMidnightmins,$model->timezone));
                }
            ],*/
            'smartTransition_duration_ms:msToS',
            'smartTransition_behavior:hgGlozoneTimeBlockSmartBehaviorIcon',
            'smartOn_switch_behavior:hgGlozoneTimeBlockSmartBehaviorIcon',
            'smartOn_motion_behavior:hgGlozoneTimeBlockSmartBehaviorIcon',
            //'time_start_sun_midnightmins',
            //'time_end_sun_midnightmins',
            //'time_start_mon_midnightmins',
            //'time_end_mon_midnightmins',
            //'time_start_tue_midnightmins',
            //'time_end_tue_midnightmins',
            //'time_start_wed_midnightmins',
            //'time_end_wed_midnightmins',
            //'time_start_thu_midnightmins',
            //'time_end_thu_midnightmins',
            //'time_start_fri_midnightmins',
            //'time_end_fri_midnightmins',
            //'time_start_sat_midnightmins',
            //'time_end_sat_midnightmins',
            //'timezone',
            //'metadata',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, HgGlozoneTimeBlock $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
            'display_name',
            [
                'attribute'=>'test',
                'header'=>'Test',
                'value'=>function(HgGlozoneTimeBlock $model) {
                    if ($model->getHasActiveSmartTransition())
                        return Html::a('Test',['/hg-glozone-time-block/test-transition','id'=>$model->id],['class'=>'btn btn-success']);
                    else
                        return '';
                },
                'format'=>'raw'
            ],

        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

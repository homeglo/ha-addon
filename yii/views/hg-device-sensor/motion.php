<?php

use app\models\HgDeviceSensor;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\HgDeviceSensorSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var \app\models\InitMotionForm $initMotionForm */

$this->title = 'Motion Sensors';
$this->params['breadcrumbs'][] = $this->title;


if ($missing) {
    echo \yii\bootstrap4\Alert::widget([
        'body' => 'Some switches do not have rooms tied to them!',
        'options' => [
            'class' => 'alert-danger',
        ],
    ]);
}

?>
<div class="hg-device-sensor-index" style="overflow-y:scroll">

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'updated_at:datetime',
            'hgHub.display_name',
            'hue_id',
            'display_name',
            'hgDeviceGroup.display_name',
            'hgGlozone.display_name',
            [
                'attribute'=>'hg_device_group_multiroom_ids',
                'value'=>function (HgDeviceSensor $model) {
                    $str = '';
                    foreach ($model->hgDeviceGroupMultirooms as $multiroom) {
                        $str.=$multiroom->hgDeviceGroup->display_name."<br/>";
                    }
                    return $str;
                },
                'header'=>'Multiroom',
                'format'=>'raw'
            ],
            [
                'attribute'=>'hg_hub_action_map_id',
                'value'=>function ($data) {
                    if (!$data['hg_hub_action_map_id'])
                        return null;
                    if ($data->hgHubActionMap->isSuccessfullyHueProgrammed)
                        $r = '<div class="text-center"><i class="fa fa-check fa-2x"></i></div>';
                    else
                        $r = '<div class="text-center"><i class="fa fa-times fa-2x"></i></div>';

                    $str = Html::a($data->hgHubActionMap->display_name.' <i class="fa fa-arrow-right"></i>',['/hg-hub-action-template/index','HgHubActionTemplateSearch[hg_hub_action_map_id]'=>$data['hg_hub_action_map_id']],['class'=>'btn btn-primary','data-pjax'=>0,'target'=>'_blank']);
                    $str.= $r;
                    return $str;
                },
                'format'=>'raw'
            ],
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, HgDeviceSensor $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                },
                'template' => '{variables} {view} {update} {delete}',
                'buttons' => [
                    'variables' => function ($url,$model,$key) {
                        return Html::a(
                            '<span class="fa fa-cog"></span>',
                            ['/hg-device-sensor-variable/','hg_device_sensor_id'=>$model->id],
                            [
                                'title' => 'Variables',
                                'data-pjax' => '0',
                            ]
                        );
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

    <div class="">
        <?php
        $models = $initMotionForm->getModels();
        echo $this->render('_init-motion-form',compact('models','initMotionForm'));
        ?>
    </div>




</div>

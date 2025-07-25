<?php

use app\models\HgHubActionTemplate;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\HgHubActionTemplateSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Hg Hub Action Templates';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-hub-action-template-index">

    <p>
        <?= Html::a('Create Hg Hub Action Template', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'updated_at:datetime',
            'hgHub.display_name',
            //'hg_version_id',
            //'hg_status_id',
            //'hg_product_sensor_type_name',
            //'name',
            [
                'attribute'=>'hg_product_sensor_type_name',
                'header'=>'Sensor',
                'format'=>'raw',
                'value'=>function(HgHubActionTemplate $model) {
                    $str = $model->hg_product_sensor_type_name;
                    foreach ($model->hgHubActionTriggers as $hgHubActionTrigger) {
                        if ($hgHubActionTrigger->hg_device_sensor_id) {
                            $str = $hgHubActionTrigger->hgDeviceSensor->display_name;
                        }
                    }
                    return $str;
                }
            ],
            'display_name',
            [
                'attribute'=>'hasHueRuleErrors',
                'header'=>'Hue Status',
                'format'=>'raw',
                'value'=>function(HgHubActionTemplate $model) {
                    if ($model->hasHueRuleErrors)
                        $r = '<div class="text-center"><i class="fa fa-times fa-2x"></i></div>';
                    else
                        $r = '<div class="text-center"><i class="fa fa-check fa-2x"></i></div>';

                    return $r;
                }
            ],
            //'multi_room',
            //'metadata',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, HgHubActionTemplate $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 },
                'template' => '{clone} {view} {update} {delete}',
                'buttons' => [
                    'clone' => function ($url) {
                        return Html::a(
                            '<span class="fa fa-copy"></span>',
                            $url,
                            [
                                'title' => 'Download',
                                'data-pjax' => '0',
                            ]
                        );
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

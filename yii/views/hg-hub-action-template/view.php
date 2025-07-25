<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\HgHubActionTemplate $hgHubActionTemplate */
/** @var \yii\data\ActiveDataProvider $hgHubActionTriggerDataProvider */

$this->title = $hgHubActionTemplate->display_name;
$this->params['breadcrumbs'][] = ['label' => 'Hg Hub Action Templates', 'url' => ['index-templates','HgHubActionTemplateSearch[hg_hub_action_map_id]'=>$hgHubActionTemplate->hg_hub_action_map_id]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="hg-hub-action-template-view">

    <?= \yii\grid\GridView::widget([
        'dataProvider' => $hgHubActionTriggerDataProvider,
        'columns' => [
            'id',
            'updated_at:datetime',
            'display_name',
            'hgDeviceSensor.display_name',
            'event_name',
            [
                'attribute'=>'event_data',
                'header'=>'Event Data',
                'value'=>function($model) {
                    return json_encode($model->event_data);
                }
            ],
            [
                'attribute'=>'time_start_default_midnightmins',
                'header'=>'Time Start',
                'value'=>function($model) {
                    return date('h:i:s A',\app\components\HelperComponent::convertMidnightMinutesToEpochTime($model->hgGlozoneStartTimeBlock->calcStartMidnightmins,$model->hgGlozoneStartTimeBlock->timezone));
                }
            ],
            [
                'attribute'=>'time_end_default_midnightmins',
                'header'=>'Time End',
                'value'=>function($model) {
                    return date('h:i:s A',\app\components\HelperComponent::convertMidnightMinutesToEpochTime($model->hgGlozoneEndTimeBlock->calcStartMidnightmins,$model->hgGlozoneEndTimeBlock->timezone));
                }
            ],
            [
                'attribute'=>'hasHueRuleErrors',
                'header'=>'Hue Status',
                'format'=>'raw',
                'value'=>function(\app\models\HgHubActionTrigger $model) {
                    if ($model->hasHueError)
                        $r = '<div class="text-center"><i class="fa fa-times fa-2x"></i></div>';
                    else
                        $r = '<div class="text-center"><i class="fa fa-check fa-2x"></i></div>';

                    return $r;
                }
            ],
            [
                'class' => \yii\grid\ActionColumn::className(),
                'urlCreator' => function ($action, \app\models\HgHubActionTrigger $model, $key, $index, $column) {
                    return \yii\helpers\Url::toRoute(['/hg-hub-action-trigger/'.$action, 'id' => $model->id]);
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

</div>

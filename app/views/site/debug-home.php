<?php
use yii\helpers\Html;

/* @var \app\models\HgHome $hgHome */
/* @var \yii\data\ArrayDataProvider $hgDeviceSensorSwitchProvider */
/* @var \yii\data\ArrayDataProvider $hgDeviceSensorMotionProvider */
/* @var \yii\data\ArrayDataProvider $hgDeviceGroupProvider */


$this->title = 'Debug Home';
?>

<div class="container-fluid" id="main">
    <div class="row row-offcanvas row-offcanvas-left">
        <!--/col-->

        <div class="col main">

            <h1>Switches</h1>
            <?= \yii\grid\GridView::widget([
                'dataProvider' => $hgDeviceSensorSwitchProvider,
                'formatter' => new \app\formatters\HgFormatter(),
                'columns' => [
                    [
                        'attribute'=>'display_name',
                    ],
                    [
                        'attribute'=>'hub_validate.rules',
                        'format'=>'checkOrX'
                    ],
                    [
                        'attribute'=>'hgHubActionMap.baseHgHubActionMap.display_name',
                    ],
                    [
                        'attribute'=>'hgDeviceGroup.display_name',
                    ],
                    [
                        'attribute'=>'hub_validate.sensor_vars',
                        'format'=>'checkOrX'
                    ],
                ],
            ]) ?>

            <h1>Motion</h1>
            <?= \yii\grid\GridView::widget([
                'dataProvider' => $hgDeviceSensorMotionProvider,
                'formatter' => new \app\formatters\HgFormatter(),
                'columns' => [
                    [
                        'attribute'=>'display_name',
                    ],
                    [
                        'attribute'=>'hub_validate.rules',
                        'format'=>'checkOrX'
                    ],
                    [
                        'attribute'=>'warning_timer',
                        'value'=>function (\app\models\HgDeviceSensor $model) {
                            return $model->getHgDeviceSensorVariable(\app\models\HgDeviceSensorVariable::MOTION_WARNING_TIMER)->value;
                        }
                    ],
                    [
                        'attribute'=>'hub_validate.motion_warning_timers',
                        'format'=>'checkOrX'
                    ],
                    [
                        'attribute'=>'hgHubActionMap.baseHgHubActionMap.display_name',
                    ],
                    [
                        'attribute'=>'hgDeviceGroup.display_name',
                    ],
                    [
                        'attribute'=>'hub_validate.scene_vars',
                        'format'=>'checkOrX'
                    ],
                ],
            ]) ?>

            <h1>Rooms</h1>
            <?= \yii\grid\GridView::widget([
                'dataProvider' => $hgDeviceGroupProvider,
                'formatter' => new \app\formatters\HgFormatter(),
                'columns' => [
                    'display_name',
                    'hgHub.display_name',
                    [
                        'attribute'=>'hue_scene_id',
                        'header'=>'Glo Parity',
                        'value'=>function(\app\models\HgDeviceGroup $model) {
                            $gloGroupsCount = $model->getHgGloDeviceGroups()->andWhere(['hg_glozone_id'=>$model->hg_glozone_id])->count();
                            $glosCount = $model->hgGlozone->getHgGlos()->andWhere(['!=','name','hg_off'])->count(); //no off
                            $str='';
                            if ($gloGroupsCount == $glosCount) {
                                $str.='<i class="fa fa-check"></i>';
                            }
                            else
                                $str.='<i class="fa fa-times"></i>';

                            return $str;
                        },
                        'format'=>'raw'
                    ],
                ],
            ]); ?>

            <!--/main col-->
        </div>

    </div>
    <!--/.container-->

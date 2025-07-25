<?php

use app\models\HgDeviceSensorVariable;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\HgDeviceSensorVariableSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var \app\models\HgDeviceSensor $hgDeviceSensor */

$this->title = 'Hg Device Sensor Variables - '.$hgDeviceSensor->display_name;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-device-sensor-variable-index">

    <p>
        <?= Html::a('Create Hg Device Sensor Variable', ['create','hg_device_sensor_id'=>$hgDeviceSensor->id ?? null], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
w
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'created_at:datetime',
            'display_name',
            //'hg_device_sensor_id',
            'variable_name',
            'value',
            'sensor_type_name',
            'hgStatus.display_name',
            'overrideHgProductSensor.model_id',
            //'description:ntext',
            //'json_data',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, HgDeviceSensorVariable $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

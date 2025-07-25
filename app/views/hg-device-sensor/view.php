<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\HgDeviceSensor $model */

$this->title = $model->display_name;
$this->params['breadcrumbs'][] = ['label' => 'Hg Device Sensors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="hg-device-sensor-view">

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <?php if ($model->hgProductSensor->isMotion) {

        echo \yii\grid\GridView::widget([
            'dataProvider' => $dataProviderAux,
            'filterModel' => $searchModelAux,
            'columns' => [
                'updated_at:datetime',
                'hgHub.display_name',
                'ha_device_id',
                'display_name',

                [
                    'class' => \yii\grid\ActionColumn::className(),
                    'urlCreator' => function ($action, \app\models\HgDeviceSensor $model, $key, $index, $column) {
                        return \yii\helpers\Url::toRoute([$action, 'id' => $model->id]);
                    },
                    'template' => '{variables} {view} {update} {delete}',
                    'buttons' => [
                        'variables' => function ($url, $model, $key) {
                            return Html::a(
                                '<span class="fa fa-cog"></span>',
                                ['/hg-device-sensor-variable/', 'hg_device_sensor_id' => $model->id],
                                [
                                    'title' => 'Variables',
                                    'data-pjax' => '0',
                                ]
                            );
                        },
                    ],
                ],
            ],
        ]);
    }
    ?>

    <?= DetailView::widget([
        'model' => $model,
        'formatter'=>new \app\formatters\HgFormatter(),
        'attributes' => [
            'id',
            'created_at:datetime',
            'updated_at:datetime',
            'hg_hub_id',
            'ha_device_id',
            'display_name',
            'hgGlozone.display_name',
            [
                'attribute'=>'variables',
                'value'=>function ($model) {
                    return '<pre>'.json_encode($model->deviceVariablesForUse,JSON_PRETTY_PRINT).'</pre>';
                },
                'format'=>'raw'
            ],
            'hgDeviceGroup.display_name',
            'hgProductSensor.display_name',
            [
                'attribute'=>'metadata',
                'value'=>function ($data) {
                    $data = json_decode($data['metadata'],true)['hue_hub_data'];
                    unset($data['capabilities']);
                    return '<pre>'.json_encode($data,JSON_PRETTY_PRINT).'</pre>';
                },
                'format'=>'raw'
            ],

        ],
    ]) ?>

</div>

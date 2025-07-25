<?php

use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\HgDeviceGroup $model */
/** @var ActiveDataProvider $hgGloDeviceLightDataProvider */

$this->title = $model->display_name;
$this->params['breadcrumbs'][] = ['label' => 'Hg Device Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="hg-device-group-view">

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

    <?= DetailView::widget([
        'model' => $model,
        'formatter'=>new \app\formatters\HgFormatter(),
        'attributes' => [
            'id',
            'created_at:datetime',
            'updated_at:datetime',
            'hg_device_group_type_id',
            'hg_glozone_id',
            'ha_device_id',
            'display_name',
            'metadata:jsonPrettyPrint',
        ],
    ]) ?>

</div>

<div class="col-md-6">
    <?= \yii\grid\GridView::widget([
        'dataProvider' => $hgGloDeviceLightDataProvider,
        'columns' => [
            'id',
            'hue_scene_id',
            'updated_at:datetime',
            'hgGlo.display_name',
            'hgDeviceLight.display_name',
            'hueX',
            'hueY',
            'hueCt',
            'bri_absolute',
        ],
    ]); ?>
</div>

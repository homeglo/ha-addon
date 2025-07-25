<?php

use yii\db\Expression;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\HgGlo $model */

$this->title = $model->display_name;
$this->params['breadcrumbs'][] = ['label' => 'Hg Glos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="hg-glo-view col-md-12">

    <h1><?= Html::encode($this->title) ?></h1>

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
        'formatter'=> new \app\formatters\HgFormatter(),
        'attributes' => [
            'id',
            'created_at:datetime',
            'updated_at:datetime',
            [
                'attribute'=>'base_hg_glo_id',
                'label'=>'Base Glo Name',
                'value'=>function(\app\models\HgGlo $model) { return $model->baseHgGlo->display_name; }
            ],
            //'base_hg_glo_id',
            //'name',
            //'hub_name',
            'display_name',
            'hgStatus.display_name',
            'write_to_hue',
            'hgGlozone.display_name',
            'hgHub.display_name',
            'hg_version_id',
            [
                    'attribute'=>'hue_ids',
                    'label'=>'Hub Rooms',
                    'value'=> function (\app\models\HgGlo $model) {
                        $str = '';
                        foreach ($model->hgGloDeviceGroups as $hgGloDeviceGroup)
                            $str.=$hgGloDeviceGroup->hub_display_name.'<br/>';
                        return $str;
                    },
                    'format'=>'raw',
            ],
            /*[
                'attribute'=>'hue_ids',
                'value'=>function($model) {
                    return '<pre>'.json_encode($model->hue_ids).'</pre>';
                },
                'format'=>'raw'
            ],*/
            //'rank',
            'hue_x',
            'hue_y',
            'brightness',
            //'metadata:jsonPrettyPrint',
        ],
    ]) ?>
    <div class="col-md-6">
        <?= \yii\grid\GridView::widget([
            'dataProvider' => $hgGloDeviceLightDataProvider,
            'columns' => [
                'id',
                'hue_scene_id',
                'hgGlo.display_name',
                'hgDeviceLight.display_name',
                'ct',
                'hue_x',
                'hue_y',
                'bri_absolute'
            ],
        ]); ?>
    </div>
</div>


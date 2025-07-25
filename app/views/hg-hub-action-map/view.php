<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\HgHubActionMap $model */

$this->title = $model->display_name;
$this->params['breadcrumbs'][] = ['label' => 'Hg Hub Action Maps', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="hg-hub-action-map-view">

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
    <div class="col-md-6">
        <?= DetailView::widget([
            'model' => $model,
            'formatter'=> new \app\formatters\HgFormatter(),
            'attributes' => [
                'id',
                'created_at:datetime',
                'updated_at:datetime',
                'preserve_hue_buttons:switchButtonNames',
                'display_name',
                'map_image_url:image',
                'hg_product_sensor_map_type',
            ],
        ]) ?>
    </div>


</div>

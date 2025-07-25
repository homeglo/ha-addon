<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\HgHub $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Hg Hubs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="hg-hub-view">

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('Scan for new devices', ['/hg-hub/scan-devices', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= \yii\grid\GridView::widget([
        'dataProvider' => $provider,
        'columns' => [
            [
                'attribute'=>'data',
                'value'=>function ($data) {
                    return '<pre>'.json_encode($data,JSON_PRETTY_PRINT).'</pre>';
                },
                'format'=>'raw'
            ]
        ],
    ]) ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'created_at',
            'updated_at',
            'hg_home_id',
            'hg_status_id',
            'display_name',
            'access_token',
            'bearer_token',
            'refresh_token',
            'token_expires_at',
            'hue_email:email',
            'hue_random',
            'notes:ntext',
            'metadata:ntext',
        ],
    ]) ?>

</div>

<div class="container">
    <div class="row">
        <div class="col-md-4">
            <h1>Daylight Sensor</h1>
            <pre><?=json_encode($model->getHueComponent()->v1GetRequest('sensors/1'),JSON_PRETTY_PRINT);?></pre>
        </div>
        <div class="col-md-4">
            <h1>Hub Config</h1>
            <pre><?=json_encode($model->getHueComponent()->v1GetRequest('config'),JSON_PRETTY_PRINT);?></pre>
        </div>
        <div class="col-md-4">
            <h1>Capabilities Config</h1>
            <pre><?=json_encode($model->getHueComponent()->v1GetRequest('capabilities'),JSON_PRETTY_PRINT);?></pre>
        </div>
    </div>

</div>

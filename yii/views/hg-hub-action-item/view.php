<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\HgHubActionItem $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Hg Hub Action Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="hg-hub-action-item-view">

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
        'attributes' => [
            'id',
            'created_at',
            'updated_at',
            'hg_hub_action_trigger_id',
            'entity',
            'operation_name',
            'operation_value_json',
            'operate_hg_device_light_group_id',
            'hg_glo_id',
            'display_name',
            'override_hue_x',
            'override_hue_y',
            'override_bri_absolute',
            'override_bri_increment_percent',
            'override_transition_duration_ms',
            'override_transition_at_time:datetime',
            'metadata:ntext',
        ],
    ]) ?>

</div>

<?php

use app\models\HgHubActionItem;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\HgHubActionItemSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Hg Hub Action Items';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-hub-action-item-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Hg Hub Action Item', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'created_at',
            'updated_at',
            'hg_hub_action_trigger_id',
            'entity',
            //'operation_name',
            //'operation_value_json',
            //'operate_hg_device_light_group_id',
            //'hg_glo_id',
            //'display_name',
            //'override_hue_x',
            //'override_hue_y',
            //'override_bri_absolute',
            //'override_bri_increment_percent',
            //'override_transition_duration_ms',
            //'override_transition_at_time:datetime',
            //'metadata:ntext',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, HgHubActionItem $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

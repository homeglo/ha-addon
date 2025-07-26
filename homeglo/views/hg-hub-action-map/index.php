<?php

use app\models\HgHubActionMap;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\HgHubActionMapSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Hg Hub Action Maps';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-hub-action-map-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Hg Hub Action Map', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter' => new \app\formatters\HgFormatter(),
        'columns' => [

            'id',
            'created_at',
            'updated_at',
            'name',
            'display_name',
            'preserve_hue_buttons:switchButtonNames',
            [
                'attribute'=>'calculate_hue_rule_cost',
                'value'=>function (HgHubActionMap $hgHubActionMap) {
                    return $hgHubActionMap->calculateHueRuleCost;
                },
                'header'=>'Rule Cost',
                'format'=>'raw'
            ],
            //'map_image_url:url',
            //'base_hg_hub_action_map_id',
            //'hg_product_sensor_map_type',
            //'hg_status_id',
            //'preserve_hue_buttons',
            //'metadata',
            [
                'attribute'=>'actions',
                'value'=>function ($data) {
                    return Html::a('&nbsp;&nbsp;',['/hg-hub-action-map/clone-map','id'=>$data['id']],['class'=>'fa fa-copy','data'=>['confirm'=>'Are you sure you want to clone?']]).
                        Html::a('&nbsp;&nbsp;',['/hg-hub-action-map/export-map','id'=>$data['id']],['class'=>'fa fa-download']);
                },
                'format'=>'raw'
            ],
            [
                'attribute'=>'actions',
                'value'=>function ($data) {
                    return Html::a('Templates',['/hg-hub-action-template/index-templates','HgHubActionTemplateSearch[hg_hub_action_map_id]'=>$data['id']],['class'=>'btn btn-primary']);
                },
                'format'=>'raw'
            ],
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, HgHubActionMap $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

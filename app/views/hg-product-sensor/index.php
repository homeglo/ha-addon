<?php

use app\models\HgProductSensor;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\HgProductSensorSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Hg Product Sensors';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-product-sensor-index">

    <p>
        <?= Html::a('Create Hg Product Sensor', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'display_name',
            'manufacturer_name',
            'product_name',
            'type_name',
            'archetype',
            'model_id',
            //'description:ntext',
            //'rank',
            //'button_count',
            //'action_map_type',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, HgProductSensor $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

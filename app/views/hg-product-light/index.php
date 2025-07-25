<?php

use app\models\HgProductLight;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\HgProductLightSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Hg Product Lights';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-product-light-index">

    <p>
        <?= Html::a('Create Hg Product Light', ['create'], ['class' => 'btn btn-success']) ?>
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
            'productid',
            'product_name',
            'archetype',
            'model_id',
            'maxlumen',
            //'description:ntext',
            //'rank',
            //'version',
            //'price',
            //'range',
            //'capability_json:ntext',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, HgProductLight $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

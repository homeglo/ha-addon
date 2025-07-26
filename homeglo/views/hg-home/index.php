<?php

use app\models\HgHome;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\HgHomeSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Hg Homes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-home-index">

    <p>
        <?= Html::a('Create Hg Home', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'created_at:datetime',
            //'name',
            'display_name',
            //'lat',
            //'lng',
            [
                'attribute'=>'hgHubs',
                'value'=>function ($model) {
                    return count($model->hgHubs);
                },
            ],
            [
                'attribute'=>'enter_home',
                'value'=>function ($model) {
                    return '<a class="btn btn-secondary" href="/site/enter-home?id='.$model['id'].'">Open Home &raquo;</a>';
                },
                'format'=>'raw',
            ],
            'hgStatus.display_name',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, HgHome $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],

        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

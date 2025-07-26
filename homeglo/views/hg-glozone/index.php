<?php

use app\models\HgGlozone;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\HgGlozoneSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Hg Glozones';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-glozone-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Hg Glozone', ['create'], ['class' => 'btn btn-success']) ?>
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
            'hg_home_id',
            'name',
            //'display_name',
            //'bed_time_weekday_midnightmins',
            //'wake_time_weekday_midnightmins',
            //'bed_time_weekend_midnightmins',
            //'wake_time_weekend_midnightmins',
            //'metadata',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, HgGlozone $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

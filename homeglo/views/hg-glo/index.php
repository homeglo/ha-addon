<?php

use app\models\HgGlo;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\HgGloSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Hg Glos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-glo-index" style="overflow-y:scroll">

    <p>
        <?= Html::a('Create Hg Glo', ['create','hg_glozone_id'=>$this->context->hg_glozone_id], ['class' => 'btn btn-success']) ?>
    </p>
    <?php /*Html::a('Populate Factory GLOs', ['populate-factory-glos'], [
        'class' => 'btn btn-danger float-right',
        'data' => [
            'confirm' => 'This will WIPE OUT existing GLOS!',
        ],
    ]); */ ?>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            //'hub_name',
            'display_name',
            //'hgStatus.display_name',
            //'hg_glozone_id',
            //'hgHub.display_name',
            //'hg_version_id',
            //'rank',
            'ct',
            'hue_x',
            'hue_y',
            'brightness',
            [
                'attribute'=>'test_glo',
                'header'=>'Test Glo',
                'value'=>function(HgGlo $model) {
                   return Html::a('Test',['/hg-glo/whole-home-test-glo','hg_glo_id'=>$model->id],[
                           'class'=>'btn btn-info',
                           'data-toggle'=>'tooltip',
                           'data-placement'=>'top',
                           'title'=>'Set all the lights in the home to this color',
                   ]);
                },
                'format'=>'raw'
            ],
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, HgGlo $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

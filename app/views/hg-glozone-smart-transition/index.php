<?php

use app\models\HgGlozoneSmartTransition;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\HgGlozoneSmartTransitionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Hg Glozone Smart Transitions';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-glozone-smart-transition-index">

    <p>
        <?= Html::a('Create Hg Glozone Smart Transition', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter' => new \app\formatters\HgFormatter(),
        'columns' => [
            'id',
            'created_at:datetime',
            'hgGlozoneTimeBlock.display_name',
            'hgDeviceGroup.display_name',
            'hgStatus.display_name',
            //'rank',
            'behavior_name:hgGlozoneTimeBlockSmartBehaviorIcon',
            //'last_trigger_at',
            //'last_trigger_status',
            //'metadata',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, HgGlozoneSmartTransition $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

<?php

use app\models\HgDeviceLight;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\HgDeviceLightSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var $available_rooms[] */
/** @var $available_hubs[] */

$this->title = 'DB Lights';
$this->params['breadcrumbs'][] = ['label'=>$this->context->home_record['display_name'],'url'=>['/site/enter-home','id'=>$this->context->home_record['id']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-device-light-index">

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?=Html::a('Sync lights','/hg-device-light/sync-lights',['class'=>'btn btn-primary']);;?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'display_name',
            'serial',
            [
                'filter' => Html::activeDropDownList($searchModel, 'primary_hg_device_group_id', $available_rooms),
                'attribute'=>'primaryHgDeviceGroup.display_name',
            ],
            'hgProductLight.display_name',
            //'metadata:ntext',
            [
                'attribute'=>'test_light',
                'header'=>'Test',
                'value'=>function(HgDeviceLight $model) {
                    return Html::a('Test',['/hg-device-light/test-light','hg_device_light_id'=>$model->id],['class'=>'btn btn-info']);
                },
                'format'=>'raw'
            ],
            [
                'class' => ActionColumn::class,
                'urlCreator' => function ($action, HgDeviceLight $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

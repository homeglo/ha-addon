<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\HgHubActionTrigger $model */
/** @var \yii\data\ActiveDataProvider $hgHubActionConditionDataProvider */
/** @var \yii\data\ActiveDataProvider $hgHubActionItemDataProvider */

$this->title = $model->display_name;
$this->params['breadcrumbs'][] = ['label' => 'Hg Hub Action Triggers', 'url' => ['/hg-hub-action-template/view','id'=>$model->hg_hub_action_template_id]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="hg-hub-action-trigger-view">

    <h1>Conditions</h1>
    <?=Html::a('Add Condition',['/hg-hub-action-condition/create','hg_hub_action_trigger_id'=>$model->id],['class'=>'btn btn-primary']);?>
    <?= \yii\grid\GridView::widget([
        'dataProvider' => $hgHubActionConditionDataProvider,
        'columns' => [
            'id',
            //'hgHub.display_name',
            'display_name',
            'property',
            'operator',
            'value',
            [
                'class' => \yii\grid\ActionColumn::className(),
                'urlCreator' => function ($action, \app\models\HgHubActionCondition $model, $key, $index, $column) {
                    return \yii\helpers\Url::toRoute(['/hg-hub-action-condition/'.$action, 'id' => $model->id]);
                }
            ],

        ],
    ]); ?>

    <h1>Actions</h1>
    <?=Html::a('Add Action',['/hg-hub-action-item/create','hg_hub_action_trigger_id'=>$model->id],['class'=>'btn btn-primary']);?>

    <?= \yii\grid\GridView::widget([
        'dataProvider' => $hgHubActionItemDataProvider,
        'columns' => [
            'id',
            //'hgHub.display_name',
            'display_name',
            'entity',
            'operation_name',
            [
                    'attribute'=>'operation_value_json',
                'value'=>function ($model) {
                    return json_encode($model->operation_value_json);
                }
            ],
            'operateHgDeviceLightGroup.display_name',
            'hgGlo.display_name',
            [
                'class' => \yii\grid\ActionColumn::className(),
                'urlCreator' => function ($action, \app\models\HgHubActionItem $model, $key, $index, $column) {
                    return \yii\helpers\Url::toRoute(['/hg-hub-action-item/'.$action, 'id' => $model->id]);
                }
            ],

        ],
    ]); ?>

    <?php if ($model->ha_device_id) { ?>
    <h1>HA Device JSON</h1>
    <pre><?=$model->ha_device_id;?></pre>
    <pre><?=json_encode($ruleHueJson,JSON_PRETTY_PRINT);?></pre>
    <?php } else { ?>
    <pre><?=json_encode($model->getJsonData(),JSON_PRETTY_PRINT);?></pre>
    <?php } ?>

</div>

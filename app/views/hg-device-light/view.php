<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\HgDeviceLight $model */
/** @var app\models\CloneBulbForm $cloneBulbForm */


$this->title = $model->display_name;
$this->params['breadcrumbs'][] = ['label' => 'Hg Device Lights', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>



<div class="hg-device-light-view">
    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?php echo Html::a('Identify Flash',['/hg-device-light/test-light','hg_device_light_id'=>$model->id],['class'=>'btn btn-info float-right']); ?>

    </p>

    <?= DetailView::widget([
        'model' => $model,
        'formatter'=>new \app\formatters\HgFormatter(),
        'attributes' => [
            'id',
            'created_at:datetime',
            'updated_at:datetime',
            'hgHub.display_name',
            'hue_id',
            'display_name',
            'isAmbiance:checkOrX',
            'primaryHgDeviceGroup.display_name',
            'hgProduct.display_name',
            'hgDeviceLightFixture.display_name',
            'isBulb',
            'metadata:jsonPrettyPrint',
        ],
    ]) ?>

</div>

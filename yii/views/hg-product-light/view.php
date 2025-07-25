<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\HgProductLight $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Hg Product Lights', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="hg-product-light-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'display_name',
            'manufacturer_name',
            'productid',
            'product_name',
            'archetype',
            'model_id',
            'maxlumen',
            'description:ntext',
            'rank',
            'version',
            'price',
            'range',
            'capability_json:ntext',
        ],
    ]) ?>

</div>

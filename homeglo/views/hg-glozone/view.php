<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\HgGlozone $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Hg Glozones', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="hg-glozone-view">

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
            'created_at',
            'updated_at',
            'hg_home_id',
            'name',
            'display_name',
            'bed_time_weekday_midnightmins',
            'wake_time_weekday_midnightmins',
            'bed_time_weekend_midnightmins',
            'wake_time_weekend_midnightmins',
            'metadata',
        ],
    ]) ?>

</div>

<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgGloSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-glo-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'created_at') ?>

    <?= $form->field($model, 'updated_at') ?>

    <?= $form->field($model, 'base_hg_glo_id') ?>

    <?= $form->field($model, 'name') ?>

    <?php // echo $form->field($model, 'hub_name') ?>

    <?php // echo $form->field($model, 'display_name') ?>

    <?php // echo $form->field($model, 'hg_status_id') ?>

    <?php // echo $form->field($model, 'hg_glozone_id') ?>

    <?php // echo $form->field($model, 'hg_hub_id') ?>

    <?php // echo $form->field($model, 'hg_version_id') ?>

    <?php // echo $form->field($model, 'hue_id') ?>

    <?php // echo $form->field($model, 'rank') ?>

    <?php // echo $form->field($model, 'hue_x') ?>

    <?php // echo $form->field($model, 'hue_y') ?>

    <?php // echo $form->field($model, 'brightness') ?>

    <?php // echo $form->field($model, 'metadata') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

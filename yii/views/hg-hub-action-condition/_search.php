<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\HgHubActionConditionSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="hg-hub-action-condition-search">

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

    <?= $form->field($model, 'hg_hub_action_trigger_id') ?>

    <?= $form->field($model, 'hg_status_id') ?>

    <?php // echo $form->field($model, 'name') ?>

    <?php // echo $form->field($model, 'display_name') ?>

    <?php // echo $form->field($model, 'property') ?>

    <?php // echo $form->field($model, 'operator') ?>

    <?php // echo $form->field($model, 'value') ?>

    <?php // echo $form->field($model, 'metadata') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

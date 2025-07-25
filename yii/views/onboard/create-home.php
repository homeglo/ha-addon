<?php

/*
 * This file is part of the 2amigos/yii2-usuario project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View                   $this
 * @var app\models\CreateHomeForm $model
 */

$this->title = 'Create Home';

?>

<div class="jumbotron">
    <h1 class="display-5 text-center">Welcome to HomeGlo!</h1>
    <h2 class="display-6 text-center">Let's setup your home.</h2>
</div>

<section class="vh-50 gradient-custom">
    <div class="container py-5 h-100">
        <div class="row justify-content-center align-items-center h-100">
            <div class="col-12 col-lg-9 col-xl-7">
                <div class="card shadow-2-strong card-registration" style="border-radius: 15px;">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="mb-4 pb-2 pb-md-0 mb-md-5">Setup your Home</h3>
                        <?php $form = ActiveForm::begin(
                            [
                                'id' => $model->formName()
                            ]
                        ); ?>

                        <?= $form->field($model, 'home_name')->textInput() ?>
                        <?= $form->field($model, 'home_address')->textInput() ?>

                        <?= $form->field($model, 'wake_time')->widget(\kartik\time\TimePicker::classname(), [
                            'pluginOptions'=>[
                                'showMeridian'=>true,
                                'minuteStep'=>1
                            ]
                        ]); ?>

                        <?= $form->field($model, 'bed_time')->widget(\kartik\time\TimePicker::classname(), [
                            'pluginOptions'=>[
                                'showMeridian'=>true,
                                'minuteStep'=>1
                            ]
                        ]); ?>

                        <?= Html::submitButton(Yii::t('usuario', 'Next >'), ['class' => 'btn btn-success btn-block']) ?>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
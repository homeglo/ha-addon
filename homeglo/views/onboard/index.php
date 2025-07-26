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
 * @var app\models\Step1Form $model
 */

$this->title = 'Create Account';

?>

<div class="jumbotron">
    <h1 class="display-3 text-center">Welcome to HomeGlo!</h1>
    <h2 class="display-5 text-center">Please make sure your Hue Hub is plugged in and turned on!</h2>
</div>

<section class="vh-50 gradient-custom">
    <div class="container py-5 h-100">
        <div class="row justify-content-center align-items-center h-100">
            <div class="col-12 col-lg-9 col-xl-7">
                <div class="card shadow-2-strong card-registration" style="border-radius: 15px;">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="mb-4 pb-2 pb-md-0 mb-md-5">Create Account</h3>
                        <?php $form = ActiveForm::begin(
                            [
                                'id' => $model->formName(),
                                'enableAjaxValidation' => true,
                                'enableClientValidation' => false,
                            ]
                        ); ?>

                        <?= $form->field($model, 'home_id')->textInput(['autofocus' => true]) ?>
                        <?= $form->field($model, 'email')->textInput() ?>

                        <?= $form->field($model, 'username')->hiddenInput(['value'=>rand(0,1111111)])->label(false) ?>
                        <?= $form->field($model, 'password')->passwordInput() ?>


                        <?= Html::submitButton(Yii::t('usuario', 'Next >'), ['class' => 'btn btn-success btn-block']) ?>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
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
 * @var \app\models\HgHub                   $hgHub
 * @var \app\models\HgHome                  $hgHome
 */

$this->title = Yii::t('usuario', 'Connect Hub');

?>

<div class="jumbotron">
    <h2 class="display-6 text-center">Let's connect your Hue Hub.</h2>
</div>

<section class="vh-50 gradient-custom">
    <div class="container py-5 h-100">
        <div class="row justify-content-center align-items-center h-100">
            <div class="col-12 col-lg-9 col-xl-7">
                <div class="card shadow-2-strong card-registration" style="border-radius: 15px;">
                    <div class="card-body p-4 p-md-5">
                        <?=Html::a('Connect Hue Hub','https://api.meethue.com/v2/oauth2/authorize?client_id='.$_ENV['HUE_CLIENT_ID'].'&response_type=code&state='.$hgHub['id'],['class'=>'btn btn-success btn-block']);?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
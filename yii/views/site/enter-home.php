<?php
use yii\helpers\Html;

/* @var \app\models\HgHome $hgHome */
/* @var \app\models\HgHub[] $hub_status */


$this->title = 'Home';
?>

<div class="container-fluid" id="main">
    <div class="row row-offcanvas row-offcanvas-left">
        <!--/col-->

        <div class="col main">

            <h1>Rooms</h1>
            <?= \yii\grid\GridView::widget([
                'dataProvider' => $hgDeviceGroupProvider,
                'formatter' => new \app\formatters\HgFormatter(),
                'columns' => [
                    [
                        'header'=>'Room',
                        'attribute'=>'hgDeviceGroup.display_name',
                    ],
                    [
                        'header'=>'Last Transition',
                        'attribute'=>'details.last_time_block_transition',
                        'value'=>function ($model) {
                            /* @var \app\models\HgGlozoneSmartTransition $hgGlozoneSmartTransition */
                            $hgGlozoneSmartTransition = $model['details']['last_time_block_transition'];
                            $hgGlozoneSmartTransitionExecute = $hgGlozoneSmartTransition->hgGlozoneSmartTransitionExecuteLast;
                            return $hgGlozoneSmartTransitionExecute->metadata['trigger_status'].' <br/>@ '.
                                date('M-d h:i:s A T',$hgGlozoneSmartTransitionExecute->updated_at).' <br/> Target Glo: '.
                                $hgGlozoneSmartTransition->hgGlozoneTimeBlock->defaultHgGlo->display_name
                                ;
                        },
                        'format'=>'raw'
                    ],
                    [
                        'header'=>'Current Glo',
                        'attribute'=>'details.current_glo',
                        'value'=>function ($model) {
                            return $model['details']['current_glo']->display_name;
                        },
                        'format'=>'raw'
                    ],
                    [
                        'header'=>'Next Transition',
                        'attribute'=>'details.upcoming_transition_time',
                        'value'=>function ($model) {
                            if ($model['details']['behavior'] == 'inactive')
                                return 'NONE';

                            return $model['details']['behavior'].'<br/> @ '.
                                $model['details']['upcoming_transition_time'].'<br/> Target Glo: '.
                                $model['details']['upcoming_glo'];
                        },
                        'format'=>'raw'
                    ],
                ],
            ]) ?>

            <a id="more"></a>
            <a class="btn btn-primary mb-1" href="/site/debug-home?hg_home_id=<?=$hgHome->id;?>">
                Debug Dashboard
                <i class="fas fa-arrow-right fa-sm"></i>
            </a>
            <a class="btn btn-primary mb-1" href="<?=$hgHome->hgHomeUsers[0]->hgUser->loginLink;?>">
                Login Link
                <i class="fas fa-arrow-right fa-sm"></i>
            </a>
            <hr>
            <!--/row-->

            <a id="more"></a>
            <hr>
            <h2 class="sub-header mt-5">Home Settings</h2>
            <div class="mb-3">
                <div class="card-deck">
                    <div class="card card-inverse card-bg-success text-center">
                        <div class="card-body">
                            <h3>Location</h3>
                            <blockquote class="card-blockquote">
                                <?php if ($hgHome->lat) { ?>
                                    <i class="fa fa-check fa-4x"></i>
                                    <p><?=$hgHome->lat.','.$hgHome->lng;?></p>
                                    <p>Sunrise: <?=date('Y-m-d h:i:s A T',date_sun_info(time(),$hgHome->lat, $hgHome->lng)['sunrise']);?></p>
                                    <p>Sunset: <?=date('Y-m-d h:i:s A T',date_sun_info(time(),$hgHome->lat, $hgHome->lng)['sunset']);?></p>
                                <?php } else { ?>
                                    <i class="fa fa-times fa-4x"></i>
                                <?php } ?>
                            </blockquote>
                        </div>
                    </div>
                    <div class="card card-inverse card-danger text-center">
                        <div class="card-body">
                            <h3>Hub Status</h3>
                            <blockquote class="card-blockquote">
                                <?php foreach ($hub_status as $name => $status) { ?>
                                    <p><?=$name;?> <?=$status?'<i class="fa fa-check fa-4x"></i>':'<i class="fa fa-times fa-4x"></i>';?></p>
                                <?php } ?>
                            </blockquote>
                        </div>
                    </div>
                </div>
            </div>
            <!--/row-->

            <a id="flexbox"></a>
            <hr>
            <h2 class="sub-header mt-5">Glozones</h2>
            <a class="btn btn-primary mb-1" href="/hg-glozone/create">
                <i class="fas fa-plus fa-sm"></i>
                Create Glozone
            </a>
            <div class="mb-3">
                <div class="card-deck">
                    <?php foreach ($hgHome->hgGlozones as $hgGlozone) { ?>
                        <div class="card card-inverse card-success text-center">
                            <div class="card-body">
                                <h3><?=$hgGlozone->display_name;?></h3>
                                <blockquote class="card-blockquote">
                                    <?php if ($hgGlozone->wake_time_weekday_midnightmins) { ?>
                                        <i class="fa fa-check fa-4x"></i>
                                        <p>Wake: <?=date('h:i:s A T',\app\components\HelperComponent::convertMidnightMinutesToEpochTime((int)$hgGlozone->wake_time_weekday_midnightmins));?></p>
                                        <p>Bed: <?=date('h:i:s A T',\app\components\HelperComponent::convertMidnightMinutesToEpochTime((int)$hgGlozone->bed_time_weekday_midnightmins));?></p>
                                    <?php } else { ?>
                                        <i class="fa fa-times fa-4x"></i>
                                    <?php } ?>
                                </blockquote>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!--/main col-->
        </div>

    </div>
    <!--/.container-->

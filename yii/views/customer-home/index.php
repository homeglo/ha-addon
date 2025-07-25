<?php

/* @var array $hub_status */
/* @var \app\models\HgHome $hgHome */
/* @var \app\models\HgDeviceGroup[] $hgDeviceGroupsData */
/* @var array $motionSensors */

$this->title = 'HomeGlo';

?>

<div class="container pt-3">
    <div class="card">
        <div class="card-header">
            <h3>Rooms</h3>
        </div>
        <div class="row">
            <?php foreach ($hgDeviceGroupsData as $hgDeviceGroupWithDetails) {
                $details = $hgDeviceGroupWithDetails['details'];
                $hgDeviceGroup = $hgDeviceGroupWithDetails['hgDeviceGroup'];
                ?>
                <div class="card-body col-md-3">

                    <div class="card">
                        <div class="card-body bg-glo-<?=$details['currentHgGlo']->name;?>">
                            <h5 class="card-title"><?=$hgDeviceGroup->display_name;?>
                                <span class="float-right"><?=$details['currentHgGlo']?->getPillHtml();?></span>
                            </h5>

                            <?php /* <p class="card-text">Room 1</p> */ ?>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">SmartOn: <b><?=$details['currentHgGlozoneTimeBlock']?->defaultHgGlo->getPillHtml();?></b></li>
                            <li class="list-group-item">
                                Next Transition:
                                <?php //$details['currentHgGlozoneTimeBlock']->defaultHgGlo->getPillHtml();?>
                                <i class="fa fa-arrow-right"></i>
                                <?=$details['upcomingHgGlozoneTimeBlock']->defaultHgGlo->getPillHtml();?>
                                @
                                <?=$details['upcomingHgGlozoneTimeBlock']->timeStartDefaultFormatted;?>
                            </li>
                            <li class="list-group-item">
                                Previous Transition:
                                <?php if ($details['lastHgGlozoneSmartTransition']->last_trigger_status==\app\models\HgGlozoneSmartTransition::RESULT_STATUS_GLO_CHANGE) { ?>
                                    <i class="fa fa-check"></i>
                                <?php } else { ?>
                                    <i class="fa fa-times" data-toggle="popover" title="Smart Transition" data-content="The transition did not happen."></i>
                                <?php } ?>
                                <i class="fa fa-arrow-right"></i>
                                <?=$details['lastHgGlozoneSmartTransition']->hgGlozoneTimeBlock->defaultHgGlo->getPillHtml();?>
                                @
                                <?php //$details['currentHgGlozoneTimeBlock']->defaultHgGlo?->getPillHtml();?>
                                    <?=$details['lastHgGlozoneSmartTransition']->timeStartDefaultFormatted;?>

                            </li>

                        </ul>
                        <?php /* ?>
                <div class="card-body">
                    <a href="#" class="card-link btn btn-info">Card link</a>
                    <a href="#" class="card-link">Another link</a>
                </div>
                <?php */ ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<br/>

<div class="container pt-3">
    <div class="card">
        <div class="card-header">
            <h3>Motion Sensors</h3>
        </div>
        <div class="row">
                <?php
                /* @var \app\models\HgDeviceSensor $hgDeviceSensor */
                foreach ($motionSensors as $hgDeviceSensor) { ?>
                        <div class="card-body col-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><?=$hgDeviceSensor->display_name;?></h5>
                                    <p class="card-text">Motion Sensor located in the <b><?=$hgDeviceSensor->hgDeviceGroup->display_name;?></b> room.</p>
                                </div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Warning Timer: <b><?=$hgDeviceSensor->getHgDeviceSensorVariable(\app\models\HgDeviceSensorVariable::MOTION_WARNING_TIMER)->value;?></b></li>
                                    <li class="list-group-item">Light Sensitivity: <b><?=$hgDeviceSensor->ambientHgDeviceSensorOne->getHgDeviceSensorVariable(\app\models\HgDeviceSensorVariable::AMBIENT_DEFAULT_DARK_THRESHOLD)->value;?></b></li>
                                    <li class="list-group-item">Motion Sensitivity: <b><?=$hgDeviceSensor->getHgDeviceSensorVariable(\app\models\HgDeviceSensorVariable::MOTION_DEFAULT_SENSITIVITY)->value;?></b></li>

                                </ul>
                            </div>
                        </div>

                <?php } ?>
        </div>

    </div>
</div>

<?php $this->registerJs(<<<EOD
$(function () {
  $('[data-toggle="popover"]').popover()
})

EOD
);
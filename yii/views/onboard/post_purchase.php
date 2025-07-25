<?php

/** @var \app\models\HgHub $hgHub */

?>

<?php if ($hgHub->isReachable) { ?>
<div class="jumbotron">
    <h1 class="display-4">HomeGlo is Live! <i class="fa fa-check"></i></h1>
    <p class="lead">Great work! HomeGlo is now active in your home.</p>
    <hr class="my-4">
    <p>For any support / questions please reach out to Ron @ </p>
</div>

<?php } else { ?>
    <div class="jumbotron">
        <h1 class="display-4">Your hub is NOT connected! <i class="fa fa-times"></i></h1>
        <p class="lead">Make sure you Hue hub is plugged in via ethernet and all 3 lights are on.</p>
        <hr class="my-4">
        <p>If you need further help please call/text Ron @ </p>
        <p class="lead">
            <a class="btn btn-primary btn-lg" onclick="location.reload();" role="button">Re-Check Hub <i class="fa fa-refresh"></i></a>
        </p>
    </div>

<?php } ?>

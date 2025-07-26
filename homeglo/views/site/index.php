<?php

/** @var yii\web\View $this */
/** @var $homes */

$this->title = 'HomeGlo Admin';

?>
<div class="site-index">

    <div class="jumbotron text-center bg-transparent">
        <h1 class="display-4">HomeGlo!</h1>

        <p class="lead">Choose a home to get started</p>
    </div>

    <div class="body-content">

        <div class="row">
            <?php foreach ($homes as $h) { ?>
            <div class="col-lg-4">
                <h2><?=$h['name'];?></h2>

                <p><?=$h['display_name'];?></p>

                <p><a class="btn btn-secondary" href="/site/enter-home?id=<?=$h['id'];?>">Open Home &raquo;</a></p>
            </div>
            <?php } ?>
        </div>

    </div>
</div>

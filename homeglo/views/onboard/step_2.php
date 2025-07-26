<?php

/** @var \app\models\HgUser $hgUser */

$tier1_link = (YII_ENV_DEV ? 'https://buy.stripe.com/test_28odUJ4s47zB32E6oo' : 'https://buy.stripe.com/bIY3gdgpS7o67ao145' );
$tier2_link = (YII_ENV_DEV ? 'https://buy.stripe.com/test_28odUJ4s47zB32E6oo' : 'https://buy.stripe.com/cN2eYV7TmaAi3YcbIK' );

?>
<div class="pricing-header px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
    <h1 class="display-4">Pricing</h1>
    <p class="lead">Software to implement HomeGlo light design with your Philips Hue hardware. Full refund if canceled in 30 days.</p>
</div>

<div class="card-deck mb-3 text-center">
    <div class="card mb-4 box-shadow">
        <div class="card-header">
            <h4 class="my-0 font-weight-normal">Starter</h4>
        </div>
        <div class="card-body">
            <h1 class="card-title pricing-card-title">$36 <small class="text-muted">/ year</small></h1>
            <ul class="mt-3 mb-4 text-left">
                <li>Up to 2 rooms</li>
                <li>Up to 4: switches + motion sensors</li>
                <li>1 Hub</li>
                <li>1 Glozone</li>
            </ul>
            <a href="<?=$tier1_link;?>?prefilled_email=<?=$hgUser->email;?>" class="btn btn-lg btn-block btn-primary">Proceed</a>
        </div>
    </div>
    <div class="card mb-4 box-shadow">
        <div class="card-header">
            <h4 class="my-0 font-weight-normal">User</h4>
        </div>
        <div class="card-body">
            <h1 class="card-title pricing-card-title">$120 <small class="text-muted">/ year</small></h1>
            <ul class="mt-3 mb-4 text-left">
                <li>Up to 15 rooms</li>
                <li>Up to 20: switches + motion sensors</li>
                <li>Up to 2 Hubs</li>
                <li>Up to 2 Glozones</li>
            </ul>
            <a href="<?=$tier2_link;?>?prefilled_email=<?=$hgUser->email;?>" class="btn btn-lg btn-block btn-primary">Proceed</a>
        </div>
    </div>
    <div class="card mb-4 box-shadow">
        <div class="card-header">
            <h4 class="my-0 font-weight-normal">All-in</h4>
        </div>
        <div class="card-body">
            <h1 class="card-title pricing-card-title">$240 <small class="text-muted">/ year</small></h1>
            <ul class="mt-3 mb-4 text-left">
                <li>Up to 30 rooms</li>
                <li>Up to 50: switches + motion sensors</li>
                <li>Up to 5 Hub</li>
                <li>Up to 3 Glozones</li>
            </ul>
            <button type="button" class="btn btn-lg btn-block btn-outline-primary disabled">Not Available</button>
        </div>
    </div>
</div>
<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\helpers\AddonHelper;
use app\helpers\IngressHelper;

/* (C) Copyright 2019 Heru Arief Wijaya (http://belajararief.com/) untuk Indonesia.*/

?>

<!-- Topbar -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>
    <!-- REMOVED: Homes button - not needed for single home local setup -->

    <div class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">


    </div>
    <div class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">

        <h1> <span class="badge badge-danger">
            <i class="fas fa-home"></i>
            <?=Yii::$app->controller->home_record->display_name ?? '';?>
        </span></h1>

    </div>

    <?php
        $jobCount = (new \yii\db\Query())->select('*')->from('queue')->count();
        $this->registerJs("$(function () {
                          $('[data-toggle=\"tooltip\"]').tooltip()
                        })"
        );
        
        // Get addon version
        $addonVersion = AddonHelper::getAddonVersion();
    ?>
    
    <?php if ($addonVersion): ?>
        <span class="badge badge-secondary mr-2">
            <i class="fas fa-code-branch"></i>
            v<?= Html::encode($addonVersion) ?>
        </span>
    <?php endif; ?>
    
    <span class="badge badge-<?=$jobCount>0?'warning':'primary';?>">
            <i class="fas fa-<?=$jobCount>0?'recycle':'check';?>"></i>
            <?=$jobCount ?? 0;?>
    </span>
    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">
        <div class="topbar-divider d-none d-sm-block"></div>
        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <?php 
            $displayMode = IngressHelper::getDisplayMode();
            $userLabel = $displayMode === 'ingress' ? 'HA User' : 
                        ($displayMode === 'standalone-ha' ? 'Local HA' : 'Local User');
            ?>
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= Html::encode($userLabel) ?></span>
                <img class="img-profile rounded-circle" src="<?= IngressHelper::getBaseUrl() ?>/images/logo-small.png">
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="<?= Url::to(['/site/enter-home', 'id' => 2]) ?>">
                    <i class="fas fa-home fa-sm fa-fw mr-2 text-gray-400"></i>
                    Home Dashboard
                </a>
                
                <?php if ($displayMode !== 'standalone'): ?>
                <div class="dropdown-divider"></div>
                <?= Html::beginForm(['/api/ha/sync/all'], 'post'); ?>
                    <?= Html::submitButton(
                        '<i class="fas fa-sync fa-sm fa-fw mr-2 text-gray-400"></i> Sync from Home Assistant',
                        ['class' => 'dropdown-item', 'encode' => false]
                    ); ?>
                <?= Html::endForm(); ?>
                <?php endif; ?>
                
                <?php if ($displayMode === 'standalone' || $displayMode === 'standalone-ha'): ?>
                <div class="dropdown-divider"></div>
                <div class="dropdown-item-text text-muted small">
                    <i class="fas fa-info-circle fa-sm fa-fw mr-2"></i>
                    Mode: <?= Html::encode(ucfirst(str_replace('-', ' ', $displayMode))) ?>
                </div>
                <?php endif; ?>
            </div>
        </li>

    </ul>

</nav>
<!-- End of Topbar -->

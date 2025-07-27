<?php
use yii\helpers\Html;
use app\helpers\IngressHelper;

/* @var $this yii\web\View */
/* @var $mode string */

$this->title = 'HomeGlo';
?>

<div class="container-fluid">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-lightbulb"></i> HomeGlo
                        <?php if (IngressHelper::hasHomeAssistantConnection()): ?>
                            <span class="badge badge-light float-right">HA Connected</span>
                        <?php else: ?>
                            <span class="badge badge-warning float-right">Standalone</span>
                        <?php endif; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <h5>Welcome to HomeGlo</h5>
                    
                    <?php if ($flash = Yii::$app->session->getFlash('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?= Html::encode($flash) ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($flash = Yii::$app->session->getFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?= Html::encode($flash) ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if (IngressHelper::hasHomeAssistantConnection()): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> 
                            <strong>Home Assistant Connection Available</strong><br>
                            You can sync devices from Home Assistant.
                        </div>
                    <?php endif; ?>
                        
                    <div class="mb-3">
                        <?= Html::a(
                            '<i class="fas fa-home"></i> Enter Home Dashboard',
                            ['hg-glo/index', 'hg_glozone_id' => 2],
                            ['class' => 'btn btn-primary btn-block']
                        ) ?>
                    </div>
                    
                    <?php if (IngressHelper::hasHomeAssistantConnection()): ?>
                        <div class="mb-3">
                            <?= Html::beginForm(IngressHelper::createUrl(['site/sync-home-assistant']), 'post'); ?>
                                <?= Html::submitButton(
                                    '<i class="fas fa-sync"></i> Sync from Home Assistant',
                                    ['class' => 'btn btn-secondary btn-block']
                                ); ?>
                            <?= Html::endForm(); ?>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <h6>Access Methods:</h6>
                    <ul class="small">
                        <li><strong>Via Home Assistant:</strong> Use the sidebar panel in Home Assistant</li>
                        <li><strong>Direct Access:</strong> http://[your-ip]:<?= $_SERVER['SERVER_PORT'] ?? '80' ?></li>
                    </ul>
                    
                    <?php if (!IngressHelper::isIngressMode()): ?>
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Note:</strong> You're accessing HomeGlo directly. 
                            For the best experience, access through Home Assistant's sidebar panel.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
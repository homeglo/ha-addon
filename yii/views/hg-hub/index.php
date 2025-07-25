<?php

use app\models\HgHub;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\HgHubSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Hg Hubs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hg-hub-index" style="overflow-y:scroll">

    <p>
        <?= Html::a('Create Hg Hub', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            [
                'format'=>'raw',
                'value'=> function($data) {
                    return Html::a("Scan Devices <i class=\"fas fa-lightbulb\"></i> <i class=\"fas fa-gamepad\"></i>", ['/hg-hub/scan-devices','id'=>$data['id']], [
                            'title' => "Scan Lights",
                            'class' => 'btn btn-xs btn-success',
                            'onClick'=>'return confirm(\'Confirm scan?\');'
                        ]).' '.
                        Html::a("Clear Hub", ['/hg-hub/clear-rules','id'=>$data['id'],'retain'=>0], [
                            'title' => "Clear Hub",
                            'class' => 'btn btn-xs btn-danger',
                            'onClick'=>'return confirm(\'This will clear ALL rules in the hub!\');'
                        ]).

                        Html::a("Sync Hub <i class=\"fas fa-arrow-down\"></i> <i class=\"fas fa-arrow-up\"></i>", ['/hg-hub/sync-down-by-hub','hg_hub_id'=>$data['id']], [
                            'title' => "Sync Hub",
                            'class' => 'btn btn-xs btn-info',
                            'onClick'=>'return confirm(\'Confirm sync?\');'
                        ])."<br/><br/>";
                     /*    Html::a("Clear Local Data <i class=\"fas fa-times\"></i>", ['/hg-hub/delete-local-hub-data','id'=>$data['id']], [
                            'title' => "Sync Hub",
                            'class' => 'btn btn-xs btn-danger',
                            'onClick'=>'return confirm(\'Confirm delete local data?\');'
                        ]);
                    */

                }
            ],
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, HgHub $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
            'created_at:datetime',
            'display_name',
            'access_token',
            'bearer_token',
            //'refresh_token',
            'token_expires_at:datetime',
            [
                    'format'=>'raw',
                    'value'=> function($data) {
                            return Html::a("Link Hue Cloud <i class=\"fas fa-link\"></i>", 'https://api.meethue.com/v2/oauth2/authorize?client_id='.$_ENV['HUE_CLIENT_ID'].'&response_type=code&state='.$data['id'], [
                                'title' => "Hue Link",
                                'class' => 'btn btn-xs btn-info',
                                'target'=>'_blank'
                            ])."<br/><br/>".
                            Html::a("Refresh Token <i class=\"fa fa-refresh\"></i>", ['/hg-hub/refresh-tokens','hub_id'=>$data['id']], [
                                'title' => "Hue Refresh",
                                'class' => 'btn btn-xs btn-primary'
                                ]);

                    }
            ],
            [
                'format'=>'raw',
                'value'=> function(HgHub $model) {
                    $valid = false;
                    try {
                        $valid = $model->getHueComponent()->v1GetRequest('capabilities');
                    } catch (\Throwable $t) {

                    }

                    if ($valid) {
                        return '1';
                    } else {
                        return '0';
                    }

                },
                'header'=>'Token Valid'
            ],
            //'hue_email:email',
            //'hue_random',
            //'notes:ntext',
            //'metadata:ntext',
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
<?php foreach ($this->context->home_hubs as $hgHub) { ?>
    <pre>php yii hello/sync-down <?=$hgHub->hg_home_id;?></pre>
<?php } ?>


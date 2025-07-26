<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\HgGlozone $model */

$this->title = 'Update Hg Glozone: ' . $model->display_name;
$this->params['breadcrumbs'][] = ['label' => 'Hg Glozones', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="row">
    <div class="hg-glozone-update col-md-6">

        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>

    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h1>DANGER ZONE</h1>
                <?php if ($model->id != \app\models\HgGlozone::HG_DEFAULT_GLOZONE)
                    echo \yii\helpers\Html::a('Destroy Glozone', ['/hg-glozone/delete','id'=>$model->id], [
                        'class' => 'btn btn-danger float-right',
                        'data' => [
                            'confirm' => 'DANGEROUS, GLOZONE WILL BE DESTROYED INCLUDING GLOS, GLO TIMES!',
                            'method' => 'post',
                        ]
                    ]);
                ?>
            </div>
        </div>
    </div>
</div>

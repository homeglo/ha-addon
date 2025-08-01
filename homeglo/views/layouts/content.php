<?php
use yii\bootstrap4\Breadcrumbs;
use yii\bootstrap4\Alert;

/* (C) Copyright 2019 Heru Arief Wijaya (http://belajararief.com/) untuk Indonesia.*/
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <?=
    Breadcrumbs::widget(
        [
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            'homeLink'=>($h = Yii::$app->session->get('home_record',FALSE)) ? [
                          'label' => $h->display_name,  // required
                          'url' => ['/site/enter-home','id'=>$h->id],      // optional, will be processed by Url::to()
                      ] : null
        ]
    ) ?>
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">

        <?php if (isset($this->blocks['content-header'])) { ?>
            <h1 class="h3 mb-0 text-gray-800"><?= $this->blocks['content-header'] ?></h1>
        <?php } else { ?>
            <h1 class="h3 mb-0 text-gray-800">
                <?php
                if ($this->title !== null) {
                    echo \yii\helpers\Html::encode($this->title);
                } else {
                    echo \yii\helpers\Inflector::camel2words(
                        \yii\helpers\Inflector::id2camel($this->context->module->id)
                    );
                    echo ($this->context->module->id !== \Yii::$app->id) ? '<small>Module</small>' : '';
                } ?>
            </h1>
        <?php } ?>


    </div>
    <!-- Content Row -->
    <div class="row">
        <div class="col-md-12">
            <?php if (Yii::$app->getSession()->getAllFlashes()) {
                foreach (Yii::$app->getSession()->getAllFlashes() as $key => $value) {
                    echo Alert::widget([
                        'options' => [
                            'class' => 'alert-' . $key,
                        ],
                        'body' => $value,
                    ]);
                }
            } ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?= $content ?>
        </div>
    </div>
</div>
<!-- /.container-fluid -->
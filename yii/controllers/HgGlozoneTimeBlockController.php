<?php

namespace app\controllers;

use app\components\HelperComponent;
use app\components\HgEngineComponent;
use app\models\HgGlo;
use app\models\HgGlozone;
use app\models\HgGlozoneSmartTransition;
use app\models\HgGlozoneSmartTransitionSearch;
use app\models\HgGlozoneTimeBlock;
use app\models\HgGlozoneTimeBlockSearch;
use app\models\HgStatus;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * HgGlozoneTimeBlockController implements the CRUD actions for HgGlozoneTimeBlock model.
 */
class HgGlozoneTimeBlockController extends GlozoneBaseController
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all HgGlozoneTimeBlock models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new HgGlozoneTimeBlockSearch();
        if ($this->hg_glozone_id) {
            $glozone_id = $this->hg_glozone_id;
        } else {
            $glozone_id = HgGlozone::HG_DEFAULT_GLOZONE;
        }
        $searchModel->hg_glozone_id = $glozone_id;
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->pagination->pageSize = 100;

        $models = $dataProvider->getModels();
        $arr = [];
        foreach ($models as $m) {
            $arr[$m->calcStartMidnightmins] = $m;
        }
        ksort($arr);

        $dataProvider = new \yii\data\ArrayDataProvider(['allModels' => $arr]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single HgGlozoneTimeBlock model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $searchModel = new HgGlozoneSmartTransitionSearch();
        $qp = ArrayHelper::merge($this->request->queryParams,['HgGlozoneSmartTransitionSearch'=>['hg_glozone_time_block_id'=>$id]]);
        $dataProvider = $searchModel->search($qp);
        $dataProvider->pagination->pageSize = 1000;

        return $this->render('view', [
            'model' => $this->findModel($id),
            'dataProvider'=>$dataProvider,
            'searchModel'=>$searchModel
        ]);
    }

    /**
     * Creates a new HgGlozoneTimeBlock model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($hg_glozone_id)
    {
        $model = new HgGlozoneTimeBlock();
        $hgGlozone = HgGlozone::findOne($hg_glozone_id);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->hg_glozone_id = $hg_glozone_id;
                if ($model->save())
                    return $this->redirect(['/hg-glozone-time-block', 'hg_glozone_id' => $hg_glozone_id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
            'hgGlozone'=>$hgGlozone
        ]);
    }

    /**
     * Updates an existing HgGlozoneTimeBlock model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = $this->request->post();
        if ($this->request->isPost) {
            foreach ($post['HgGlozoneTimeBlock'] as $key => $value) {
                if ($model->{$key} == $value) {
                    unset($post['HgGlozoneTimeBlock'][$key]);
                }
            }

            if ($model->load($post) ) {
                if ($model->save())
                    return $this->redirect(['/hg-glozone-time-block', 'hg_glozone_id' => $model->hg_glozone_id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing HgGlozoneTimeBlock model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $hg_glozone_id = $model->hg_glozone_id;
        $model->delete();

        return $this->redirect(['/hg-glozone-time-block', 'hg_glozone_id' => $hg_glozone_id]);
    }

    /**
     * Finds the HgGlozoneTimeBlock model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return HgGlozoneTimeBlock the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HgGlozoneTimeBlock::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionTestTransition($id)
    {
        $model = $this->findModel($id);
        foreach (HgGlozoneSmartTransition::getSchedulerQuery()->andWhere(['hg_glozone_time_block_id'=>$id])->all() as $hgGlozoneSmartTransitionRoom) {
            $engine = new HgEngineComponent($hgGlozoneSmartTransitionRoom->hgDeviceGroup->hg_hub_id);
            try {
                $engine->processSmartTransition($hgGlozoneSmartTransitionRoom);
            } catch (\Throwable $t) {
                \Yii::error($t->getMessage(),__METHOD__);
            }

        }
        \Yii::$app->session->setFlash('success','Deployed transition test');
        return $this->redirect(['/hg-glozone-time-block','hg_glozone_id'=>$model->hg_glozone_id]);
    }

    public function actionGenSched()
    {
        header("Content-type: image/png");
        $img_width = 1400;
        $img_height = 500;

        $img = imagecreatetruecolor($img_width, $img_height);
        $black = imagecolorallocate($img, 0, 0, 0);
        $white = imagecolorallocate($img, 255, 255, 255);
        $gray = imagecolorallocate($img, 211, 211, 211);
        $red   = imagecolorallocate($img, 255, 0, 0);
        $green = imagecolorallocate($img, 0, 255, 0);
        $blue  = imagecolorallocate($img, 0, 200, 250);
        $orange = imagecolorallocate($img, 255, 200, 0);
        $brown = imagecolorallocate($img, 220, 110, 0);

        $gap_left = .1;
        $gap_right = .05;
        $gap_top = .1;
        $gap_bottom = .2;

        $chart_width = $img_width*(1-($gap_left+$gap_right));
        $chart_height = $img_height*(1-($gap_top+$gap_bottom));

        imagesetthickness($img, 2);

        //Set background
        imagefill($img, 0, 0, $gray);

        //set the axis (X)
        imageline($img, $img_width*$gap_left, $img_height*.8, $img_width*(1-$gap_right), $img_height*.8, $black);

        //set the axis (Y)
        imageline($img, $img_width*.1, $img_height*.8, $img_width*.1, $img_height*$gap_top, $black);

        //Add ticks (X) HH
        for ($x=0;$x<24;$x++) {
            $space = $chart_width/24;
            $hour_offset = $space*$x;

            imageline($img, $img_width*($gap_left)+$hour_offset, $img_height*.825, $img_width*($gap_left)+$hour_offset, $img_height*.8, $black);
            imagestring($img, 200, $img_width*($gap_left)+$hour_offset-5, $img_height*.835, $x, $black);
        }

        //Add ticks (X) 15M
        for ($y=0;$y<96;$y++) {
            $space = $chart_width/96;
            $min_15_offset = $space*$y;

            imageline($img, $img_width *($gap_left)+$min_15_offset, $img_height*.815, $img_width*($gap_left)+$min_15_offset, $img_height*.8, $black);
        }

        $midnightmins_start = 60*2;
        $midnightmins_end = 60*6;
        $brightness = .2;
        $color = $red;

        $midnightmins_start_offset = $chart_width/1440*$midnightmins_start;
        imageline($img,
            $img_width*($gap_left)+$midnightmins_start_offset,
            $img_height*(1-$gap_bottom),
            $img_width*($gap_left)+$midnightmins_start_offset,
            $img_height*($gap_top)+($chart_height* (1-$brightness)),
            $color);

        $midnightmins_end_offset = $chart_width/1440*$midnightmins_end;
        imageline($img,
            $img_width*($gap_left)+$midnightmins_end_offset,
            $img_height*(1-$gap_bottom),
            $img_width*($gap_left)+$midnightmins_end_offset,
            $img_height*($gap_top)+($chart_height* (1-$brightness)),
            $color);

        imagefilledrectangle($img,
            $img_width*($gap_left)+$midnightmins_start_offset,
            $img_height*(1-$gap_bottom),
            $img_width*($gap_left)+$midnightmins_end_offset,
            $img_height*($gap_top)+($chart_height* (1-$brightness)),
            $color);

/*
        imagefilledrectangle($img, $img_width*2/10, $img_height*5/10, $img_width*4/10, $img_height*8/10, $red);
        imagefilledrectangle($img, $img_width*4/10 - 2, $img_height*5/10, $img_width*8/10, $img_height*8/10, $red);

        imagerectangle($img, $img_width*2/10, $img_height*5/10, $img_width*4/10, $img_height*8/10, $black);
        imagerectangle($img, $img_width*4/10 - 2, $img_height*5/10, $img_width*8/10, $img_height*8/10, $black);

        imagepolygon($img, [$img_width*3/10, $img_height*2/10, $img_width*2/10, $img_height*5/10, $img_width*4/10, $img_height*5/10], 3, $black);
*/


        imagepng($img);
    }
}

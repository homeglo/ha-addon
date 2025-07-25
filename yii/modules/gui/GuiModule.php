<?php

namespace app\modules\gui;

use yii\base\Module;

class GuiModule extends Module
{
    public $controllerNamespace = 'app\modules\gui\controllers';
    public $layoutPath = 'app\modules\gui\layouts';

    public function init()
    {
        parent::init();
        // custom initialization code goes here
    }
}
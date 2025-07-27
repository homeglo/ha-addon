<?php
use hoaaah\sbadmin2\widgets\Menu;

if ($home_record = Yii::$app->session->get('home_record')) { //we are inside a home


    $glozones = \app\models\HgGlozone::find()->where(['hg_home_id'=>$home_record->id])->all();
    $glozoneItems = [];
    foreach ($glozones as $hgGlozone) {
        $glozoneItems[] = [
            'label' => $hgGlozone->display_name,
            'icon' => 'fa fa-circle-o', // optional, default to "fa fa-circle-o
            'visible' => true, // optional, default to true
            'subMenuTitle' => 'Glozone',
            'items' => [
                [
                    'label' => '('.$hgGlozone->getHgGlos()->count().') Glos',
                    'icon'=>'fa fa-heart',
                    'url' => ['/hg-glo/index','hg_glozone_id'=>$hgGlozone->id], //  Array format of Url to, will be not used if have an items
                ],
                [
                    'label' => '('.$hgGlozone->getHgGlozoneTimeBlocks()->count().') Glo Times',
                    'icon'=>'fa fa-clock',
                    'url' => ['/hg-glozone-time-block','hg_glozone_id'=>$hgGlozone->id], //  Array format of Url to, will be not used if have an items
                ],
                [
                    'label' => '('.$hgGlozone->getHgDeviceGroups()->count().') Rooms',
                    'icon'=>'fa fa-object-group',
                    'url' => ['/hg-device-group','hg_glozone_id'=>$hgGlozone->id], //  Array format of Url to, will be not used if have an items
                ],
                [
                    'label' => '('.$hgGlozone->getHgDeviceSensors()->joinWith('hgProductSensor')->where(['type_name'=>\app\models\HgProductSensor::TYPE_NAME_HUE_SWITCH])->count().') Switches',
                    'icon'=>'fa fa-gamepad',
                    'url' => ['/hg-device-sensor/switch','hg_glozone_id'=>$hgGlozone->id], //  Array format of Url to, will be not used if have an items
                ],
                [
                    'label' => '('.$hgGlozone->getHgDeviceSensors()->joinWith('hgProductSensor')->where(['type_name'=>\app\models\HgProductSensor::TYPE_NAME_HUE_MOTION_SENSOR])->count().') Motion',
                    'icon'=>'fa fa-camera',
                    'url' => ['/hg-device-sensor/motion','hg_glozone_id'=>$hgGlozone->id], //  Array format of Url to, will be not used if have an items
                ],
                [
                    'label' => 'Settings',
                    'url' => ['/hg-glozone/update','id'=>$hgGlozone->id], //  Array format of Url to, will be not used if have an items
                    'icon' => 'fas fa-fw fa-cog', // optional, default to "fa fa-circle-o
                    'visible' => true, // optional, default to true
                ],
            ]
        ];
    }


    $sideBarItems = [
           /* [
                'label' => $home_record->display_name,
                'url' => ['/site/enter-home','id'=>$home_record['id']], //  Array format of Url to, will be not used if have an items
                'icon' => 'fas fa-fw fa-home', // optional, default to "fa fa-circle-o
                'visible' => true, // optional, default to true
                // 'options' => [
                //     'liClass' => 'nav-item',
                // ] // optional
            ],
            [
                'label' => 'Home Settings',
                'url' => ['/hg-home/update','id'=>$home_record['id']], //  Array format of Url to, will be not used if have an items
                'icon' => 'fas fa-fw fa-cog', // optional, default to "fa fa-circle-o
                'visible' => true, // optional, default to true
            ],*/
            // REMOVED: Sync Hue Hub Data - not needed for Home Assistant integration
            [
                'type' => 'divider'
            ]
        ];

    $sideBarItems = \yii\helpers\ArrayHelper::merge(
        $sideBarItems,
        $glozoneItems,
            [
                [
                    'type' => 'divider', // divider or sidebar, if not set then link menu
                ],
                [
                    'label' => 'Lights',
                    'icon'=>'fa fa-lightbulb',
                    'url' => ['/hg-device-light'], //  Array format of Url to, will be not used if have an items
                ],
                [
                    'label' => 'Logic',
                    'icon'=>'fa fa-brain',
                    'url' => ['/hg-hub-action-template'], //  Array format of Url to, will be not used if have an items
                ]
                [
                    'type'=>'divider'
                ],
                // REMOVED: Hue Hub Remote Data - not needed for Home Assistant integration
            ]);

} else { //outside of a home

    $sideBarItems = [
        // REMOVED: Homes - using single default home for local Home Assistant setup
        [
            'label' => 'Base Glos',
            'url' => ['/hg-glo'], //  Array format of Url to, will be not used if have an items
            'icon' => 'fas fa-fw fa-heart', // optional, default to "fa fa-circle-o
            'visible' => true, // optional, default to true
            // 'options' => [
            //     'liClass' => 'nav-item',
            // ] // optional
        ],
        [
            'label' => 'Base Glozone Settings',
            'url' => ['/hg-glozone/update','id'=>\app\models\HgGlozone::HG_DEFAULT_GLOZONE], //  Array format of Url to, will be not used if have an items
            'icon' => 'fas fa-fw fa-cog', // optional, default to "fa fa-circle-o
            'visible' => true, // optional, default to true
            // 'options' => [
            //     'liClass' => 'nav-item',
            // ] // optional
        ],
        [
            'label' => 'Base Time Blocks',
            'url' => ['/hg-glozone-time-block'], //  Array format of Url to, will be not used if have an items
            'icon' => 'fas fa-fw fa-clock', // optional, default to "fa fa-circle-o
            'visible' => true, // optional, default to true
            // 'options' => [
            //     'liClass' => 'nav-item',
            // ] // optional
        ],
        [
            'label' => 'Base Action Maps',
            'url' => ['/hg-hub-action-map'], //  Array format of Url to, will be not used if have an items
            'icon' => 'fas fa-fw fa-clock', // optional, default to "fa fa-circle-o
            'visible' => true, // optional, default to true
            // 'options' => [
            //     'liClass' => 'nav-item',
            // ] // optional
        ],
        [
            'label' => 'Default Sensor Variables',
            'url' => ['/hg-device-sensor-variable/index'], //  Array format of Url to, will be not used if have an items
            'icon' => 'fas fa-fw fa-cog', // optional, default to "fa fa-circle-o
            'visible' => true, // optional, default to true
            // 'options' => [
            //     'liClass' => 'nav-item',
            // ] // optional
        ],
        [
            'label' => 'Products',
            'icon' => 'fa fa-box', // optional, default to "fa fa-circle-o
            'visible' => true, // optional, default to true
            // 'subMenuTitle' => 'Menu 2 Item', // optional only when have submenutitle, if not exist will not have subMenuTitle
            'items' => [
                [
                    'label' => 'Sensors',
                    'url' => ['/hg-product-sensor'], //  Array format of Url to, will be not used if have an items
                ],
                [
                    'label' => 'Lights',
                    'icon' => 'fa fa-lightbulb',
                    'url' => ['/hg-product-light'], //  Array format of Url to, will be not used if have an items
                ],
            ]
        ],
    ];
}


//so i don't accidentally do shit on prod
if (!YII_ENV_PROD) {
    $ulClass = 'bg-gradient-danger';
    $name = 'LightLab';
}
else {
    $ulClass = 'bg-gradient-primary';
    $name = 'HomeGlo';
}


echo Menu::widget([
    'options' => [
        'ulClass' => "navbar-nav $ulClass sidebar sidebar-dark accordion",
        'ulId' => "accordionSidebar"
    ], //  optional
    'brand' => [
        'url' => ['/'],
        'content' => <<<HTML
            <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-lightbulb"></i>
            </div>
            <div class="sidebar-brand-text mx-1">$name (BETA)</div>        
HTML
    ],
    'items' => $sideBarItems
]);
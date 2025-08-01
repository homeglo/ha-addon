<?php

namespace app\components;

use app\exceptions\HueSceneDoesNotExistInRoomException;
// use app\jobs\AsyncHueRequestJob; // REMOVED: No longer pushing async updates to Hue hub
use app\models\HgDeviceGroup;
use app\models\HgDeviceLight;
use app\models\HgDeviceSensor;
use app\models\HgDeviceSensorVariable;
use app\models\HgGlo;
use app\models\HgGloDeviceGroup;
use app\models\HgGloDeviceLight;
use app\models\HgGlozoneSmartTransition;
use app\models\HgGlozoneSmartTransitionExecute;
use app\models\HgGlozoneTimeBlock;
use app\models\HgHub;
use app\models\HgHubActionCondition;
use app\models\HgHubActionMap;
use app\models\HgHubActionTemplate;
use app\models\HgHubActionTrigger;
use app\models\HgProductSensor;
use app\models\HgStatus;
use app\models\HgVersion;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * TODO: MAJOR REFACTOR REQUIRED FOR HOME ASSISTANT INTEGRATION
 * 
 * This component contains extensive Hue hub synchronization logic that needs
 * to be updated for Home Assistant integration:
 * 
 * 1. Replace all hue_id references with ha_device_id
 * 2. Remove or update methods that push configurations to Hue hub
 * 3. Update hub communication logic for Home Assistant API
 * 4. Preserve manual control functionality where appropriate
 * 
 * Many methods in this class directly sync configurations to the Hue hub
 * and will need significant updates for Home Assistant compatibility.
 */
class HgEngineComponent extends Component {
    private ?int $_hg_hub_id;
    private ?int $_hg_device_group_id;
    private int $_hg_version_id;

    private ?HgHub $_hgHub;
    private ?HgDeviceGroup $_hgDeviceGroup;
    private ?HomeAssistantComponent $_haComponent = null;

    function __construct($hg_hub_id, $_hg_device_group_id=NULL, $_hg_version_id=HgVersion::HG_VERSION_MANUAL_ENTRY,)
    {
        $this->_hg_hub_id = $hg_hub_id;
        $this->_hg_device_group_id = $_hg_device_group_id;
        $this->_hg_version_id = $_hg_version_id;

        if ($this->_hg_hub_id) {
            $this->_hgHub = HgHub::findOne($this->_hg_hub_id);
        } else {
            $this->_hgHub = null;
            // Initialize Home Assistant component when no hub is provided
            $this->_haComponent = new HomeAssistantComponent();
        }
        $this->_hgDeviceGroup = HgDeviceGroup::findOne($this->_hg_device_group_id);
        parent::__construct();
    }

    /**
     * TODO: REMOVE FOR HOME ASSISTANT INTEGRATION
     * This method creates sensor variables in Hue hub - no longer needed for Home Assistant
     * 
     * create these weird sensor variable in hue hub
     */
    public function createSensorVars($hg_device_sensor_ids=[])
    {
        $hueApi = new HueSyncComponent($this->_hgHub);
        $q = HgDeviceSensor::find()->where(['hg_hub_id'=>$this->_hg_hub_id]);
        // TODO: Remove hue_sensor_variable_id logic for Home Assistant integration
        $q->andWhere(['IS','hue_sensor_variable_id',NULL]);
        if ($hg_device_sensor_ids) {
            $q->andWhere(['id'=>$hg_device_sensor_ids]);
        }

        foreach ($q->all() as $hgSensor) {
           $id = $hueApi->createSensorVariable(['name'=>str_replace(' ','',$hgSensor->display_name).'Variable']);
           $hgSensor->hue_sensor_variable_id = $id;
           $hgSensor->save();

           // TODO: Update for Home Assistant - motion scene creation no longer needed
           // if ($hgSensor->hgProductSensor->type_name == HgProductSensor::TYPE_NAME_HUE_MOTION_SENSOR) {
           //     // Motion scene creation logic will be replaced with HA integration
           // }

        }

        return HgDeviceSensor::find()->where(['hg_hub_id'=>$this->_hg_hub_id])->andWhere(['IS NOT','hue_sensor_variable_id',NULL])->all();
    }

    /**
     * Configure glo for the room
     *
     * 1. populate hg_glo_device_light table, calculate overrides and populate in this table
     *   - fixture, light product type, light group type influence this
     * 2. Loop through groups(:is_room=true), loop through scenes and create scene/group combo
     */
    public function generateGloDeviceConfiguration($hg_device_group_id=NULL, $hg_glo_id=NULL)
    {
        $hgDeviceGroupQuery = HgDeviceGroup::find()->where(['hg_hub_id'=>$this->_hg_hub_id,'is_room'=>1]);
        if ($hg_device_group_id) {
            $hgDeviceGroupQuery->andWhere(['id'=>$hg_device_group_id]);
        }
        $lights = [];
        foreach ($hgDeviceGroupQuery->all() as $hgDeviceGroup) {
            $gloQuery = HgGlo::find()->where(['hg_glozone_id'=>$hgDeviceGroup->hg_glozone_id]);

            if ($hg_glo_id)
                $gloQuery->andWhere(['id'=>$hg_glo_id]);

            foreach ($gloQuery->all() as $hgGlo) {
                $lights[$hgDeviceGroup->id][$hgGlo->id] = [];
                foreach (HgGloDeviceLight::find()->where(['hg_device_group_id'=>$hgDeviceGroup->id,'hg_glo_id'=>$hgGlo->id])->all() as $hgGLoDeviceLight) {

                    //Override calculations go here.
                    $hgGLoDeviceLight = HgEngineComponent::calculateAndSetOverrides($hgGLoDeviceLight);

                    $lights[$hgDeviceGroup->id][$hgGlo->id][$hgGLoDeviceLight->id] = [
                        'on'=> (bool) $hgGLoDeviceLight->on ?? true,
                        'bri'=> $hgGLoDeviceLight->bri_absolute ?? $hgGlo->brightness,
                    ];

                    //determine ct or xy wheel
                    if ($hgGLoDeviceLight->hueCt || $hgGLoDeviceLight->hgDeviceLight->isAmbiance) {
                        $mode = [
                            'ct'=> (int) $hgGLoDeviceLight->hueCt
                        ];
                    } else {
                        $mode = [
                            'xy'=>[
                            (float) ($hgGLoDeviceLight->hueX),
                            (float) ($hgGLoDeviceLight->hueY),
                        ]];
                    }
                    $lights[$hgDeviceGroup->id][$hgGlo->id][$hgGLoDeviceLight->id] =
                        ArrayHelper::merge($lights[$hgDeviceGroup->id][$hgGlo->id][$hgGLoDeviceLight->id],$mode);
                }
            }
        }

        /**
         * [<group_id>] => [
         *      [glo_id] => [
         *          [light_id] => [
         *              <lights>
         *          ]
         *      ]
         * ]
         */
        return $lights;
    }

    public static function calculateAndSetOverrides(HgGloDeviceLight $hgGloDeviceLight)
    {
        //process and save the overrides in the table
        return $hgGloDeviceLight;
    }



    /**
     * @param HgDeviceSensor $hgDeviceSensor
     * @return array
     * @throws \app\exceptions\HueApiException
     */
    public function deleteSensorRulesAndVariables(HgDeviceSensor $hgDeviceSensor, $preserve_buttons=[])
    {
        $array = [];
        $deleteSensorVar = true;

        foreach ($hgDeviceSensor->hgHubActionTriggers as $hgHubActionTrigger) {

            if ($hue_id = $hgHubActionTrigger->hue_id) {

                //preserve rules
                if (in_array($hgHubActionTrigger->hueButtonId,$preserve_buttons)) {
                    $deleteSensorVar = false;
                    continue;
                }

                try {
                    $this->_hgHub->getHueComponent()->v1DeleteRequest('rules/'.$hue_id);
                } catch (\Throwable $t) {
                    Yii::error($t->getMessage(),__METHOD__);
                }

                $hgHubActionTrigger->hue_id = NULL;
                $hgHubActionTrigger->save();
                $array[] = 'rule_'.$hue_id;
            }
        }

        if ($hgDeviceSensor->hue_sensor_variable_id && $deleteSensorVar) {

            try {
                $this->_hgHub->getHueComponent()->v1DeleteRequest('sensors/'.$hgDeviceSensor->hue_sensor_variable_id);
            } catch (\Throwable $t) {
                Yii::error($t->getMessage(),__METHOD__);
            }

            $array[] = 'sensor_'.$hgDeviceSensor->hue_sensor_variable_id;

            //we need to set this to null now
            $hgDeviceSensor->hue_sensor_variable_id = NULL;
            $hgDeviceSensor->save();
        } else if ($hgDeviceSensor->hue_sensor_variable_id) {
            $this->_hgHub->getHueComponent()->v1PutRequest('sensors/'.$hgDeviceSensor->hue_sensor_variable_id.'/state',['status'=>0]);
        }

        //delete any legacy switch rules
        $rules = $this->_hgHub->getHueComponent()->v1GetRequest('rules');

        $switchRules = [];
        foreach ($rules as $id => $r) {
            foreach ($r as $key => $value) {
                if ($key == 'conditions') {
                    foreach ($value as $condition) {

                        if (stripos($condition['address'],'/sensors/'.$hgDeviceSensor->hue_id.'/state') !== FALSE) {

                            if ($preserve_buttons) {
                                //If buttonevent AND the button value (e.g. 4002) move on to the next one
                                if ( (stripos($condition['address'],'buttonevent') !== FALSE) &&
                                    in_array($condition['value'],$preserve_buttons)) {
                                    continue 2; //Exclude!
                                }
                            }
                            $switchRules[$id] = $r;
                        }
                    }
                }
            }
        }

        foreach ($switchRules as $hue_id => $rules) {
            try {
                $this->_hgHub->getHueComponent()->v1DeleteRequest('rules/'.$hue_id);
            } catch (\Throwable $t) {
                Yii::error($t->getMessage(),__METHOD__);
            }
        }

        // TODO: Update for Home Assistant - motion scene deletion no longer needed
        // Previous logic deleted Hue motion scenes, will be replaced with HA integration cleanup if needed

        return $array;
    }

    /**
     * Write the sensor rules to the hub, and populate things
     */
    public function writeSensorRules($hg_hub_action_map_ids=[])
    {
        $a = [];
        foreach (HgHubActionTemplate::find()->where(['hg_hub_id'=>$this->_hg_hub_id,'hg_hub_action_map_id'=>$hg_hub_action_map_ids])->all() as $hgHubActionTemplate) {
            foreach ($hgHubActionTemplate->hgHubActionTriggers as $hgHubActionTrigger) {
                switch ($hgHubActionTemplate->platform) {
                    case 'hue':
                        $array = $this->writeHueRules($hgHubActionTrigger);
                        $a[] = $array;
                        break;
                }
            }
        }
        Yii::info(json_encode($a),__METHOD__);
        return $a;
    }

    public function writeHueRules(HgHubActionTrigger $hgHubActionTrigger)
    {
        $array = $hgHubActionTrigger->prepHueRule();
        $hueComponent = new HueComponent($this->_hgHub->access_token,$this->_hgHub->bearer_token);

        try {
            if ($hgHubActionTrigger->hue_id) { //updating
                $hueComponent->v1PutRequest('rules/'.$hgHubActionTrigger->hue_id,$array);
                $hgHubActionTrigger->touch('updated_at');
                $hgHubActionTrigger->hgHubActionTemplate->touch('updated_at');
                return $array;
            } else {
                $r = $hueComponent->v1PostRequest('rules',$array);
                $hgHubActionTrigger->hg_hub_id = $this->_hgHub->id;
                $hgHubActionTrigger->hue_id = $r[0]['success']['id'];
                if (!$hgHubActionTrigger->save()) {
                    Yii::error(HelperComponent::getFirstErrorFromFailedValidation($hgHubActionTrigger),__METHOD__);
                }
                return $array;
            }
        } catch (\Throwable $t) {
            \Sentry\captureException($t);
            \Yii::error($t->getMessage(),__METHOD__);
            $hgHubActionTrigger->appendJsonData(['error'=>$t->getMessage()]);
        }
    }

    /**
     * @param HgGlozoneSmartTransition $hgGlozoneSmartTransition
     * @param HgGlozoneSmartTransitionExecute|null $hgGlozoneSmartTransitionExecute
     * @return string|null
     * @throws HueSceneDoesNotExistInRoomException
     */
    public function processSmartTransition(HgGlozoneSmartTransition $hgGlozoneSmartTransition, HgGlozoneSmartTransitionExecute $hgGlozoneSmartTransitionExecute=NULL)
    {
        Yii::info('------------BEGIN--------'.$hgGlozoneSmartTransition->hgGlozoneTimeBlock->timeStartDefaultFormatted.'----TimeBlock:'.$hgGlozoneSmartTransition->hgGlozoneTimeBlock->display_name.'----Room:'.$hgGlozoneSmartTransition->hgDeviceGroup->display_name.'----Behavior'.$hgGlozoneSmartTransition->behavior_name.'----Glo:'.$hgGlozoneSmartTransition->hgGlozoneTimeBlock->defaultHgGlo->display_name,__METHOD__);
        
        $hgDeviceGroup = $hgGlozoneSmartTransition->hgDeviceGroup; //Current room
        $hgGlo = $hgGlozoneSmartTransition->hgGlozoneTimeBlock->defaultHgGlo;
        $previousHgGlo = $hgGlozoneSmartTransition->hgGlozoneTimeBlock->previousSequentialTimeBlock->defaultHgGlo;

        // Get light data from HA for this specific device group
        $hueLightsData = [];
        if ($this->_haComponent) {
            try {
                // First get all device lights for this group
                $hgDeviceLights = HgDeviceLight::find()
                    ->where(['primary_hg_device_group_id' => $hgDeviceGroup->id])
                    ->all();
                
                // Collect all HA entity IDs for this group
                $groupEntityIds = [];
                foreach ($hgDeviceLights as $deviceLight) {
                    if ($deviceLight->ha_device_id) {
                        $entities = $this->_haComponent->getDeviceLightEntities($deviceLight->ha_device_id);
                        foreach ($entities as $entityId) {
                            $groupEntityIds[$entityId] = $deviceLight->id; // Map entity to device light
                        }
                    }
                }
                
                // Now get states only for these entities
                $states = $this->_haComponent->getStates();
                foreach ($states as $state) {
                    if (isset($groupEntityIds[$state['entity_id']])) {
                        // Convert HA state to Hue-like format for compatibility
                        // Use the device light ID as the key to maintain compatibility with existing logic
                        $deviceLightId = $groupEntityIds[$state['entity_id']];
                        
                        // Determine color mode based on HA attributes
                        $colormode = 'ct'; // default
                        if (isset($state['attributes']['color_mode'])) {
                            if ($state['attributes']['color_mode'] === 'color_temp') {
                                $colormode = 'ct';
                            } else if ($state['attributes']['color_mode'] === 'xy') {
                                $colormode = 'xy';
                            }
                        }
                        
                        $hueLightsData[$deviceLightId] = [
                            'state' => [
                                'on' => $state['state'] === 'on',
                                'reachable' => $state['state'] !== 'unavailable',
                                'bri' => isset($state['attributes']['brightness']) ? $state['attributes']['brightness'] : 0,
                                'ct' => isset($state['attributes']['color_temp']) ? $state['attributes']['color_temp'] : null,
                                'xy' => isset($state['attributes']['xy_color']) ? $state['attributes']['xy_color'] : null,
                                'colormode' => $colormode
                            ],
                            'entity_id' => $state['entity_id'] // Keep entity ID for reference
                        ];
                    }
                }
            } catch (\Exception $e) {
                Yii::error('Failed to get HA states: ' . $e->getMessage(), __METHOD__);
            }
        }

        //pull all bulbs in scene
        $previousHgGLoDevices = HgGloDeviceLight::find()->where(['hg_hub_id'=>$this->_hg_hub_id,'hg_device_group_id'=>$hgDeviceGroup->id,'hg_glo_id'=>$previousHgGlo->id])->all();
        
        Yii::info('Found ' . count($previousHgGLoDevices) . ' previous glo devices for comparison', __METHOD__);
        Yii::info('Found ' . count($hueLightsData) . ' current light states from HA', __METHOD__);

        $return_status = NULL;
        switch ($hgGlozoneSmartTransition->behavior_name) {
            case HgGlozoneTimeBlock::SMARTTRANSITION_CYCLE_ON:
                if (empty($previousHgGLoDevices)) {
                    Yii::info('__SMARTTRANSITION_CYCLE_ON__: No previous glo set, bail.',__METHOD__);
                    $return_status = HgGlozoneSmartTransition::RESULT_STATUS_NO_GLODEVICELIGHTS_FOUND_TO_COMPARE;
                    break;
                }
                //Check if any lights in the existing group are on
                $anyOn = false;
                // For HA, check if any lights in the group are on
                foreach ($hueLightsData as $lightData) {
                    if ($lightData['state']['on']) {
                        $anyOn = true;
                        break;
                    }
                }
                if ($anyOn) { //lights are on!
                    Yii::info('__SMARTTRANSITION_CYCLE_ON__: Lights are ON: '.$hgDeviceGroup->display_name,__METHOD__);
                    Yii::info('__SMARTTRANSITION_CYCLE_ON__: Previous Glo Name: '.$previousHgGlo->display_name,__METHOD__);

                    if ( ($this->compareInCycleColors($previousHgGLoDevices,$hueLightsData,$hgGlozoneSmartTransitionExecute) )) { //colors are matching previous!
                        Yii::info('__SMARTTRANSITION_CYCLE_ON__: In cycle compare Success! Executing: '.$hgGlozoneSmartTransition->behavior_name,__METHOD__);
                        Yii::info('__SMARTTRANSITION_CYCLE_ON__: New Glo Name: '.$hgGlo->display_name,__METHOD__);

                        $this->executeTurnGloOn($hgGlozoneSmartTransition);

                        $return_status = HgGlozoneSmartTransition::RESULT_STATUS_GLO_CHANGE;
                    } else {
                        Yii::info('__SMARTTRANSITION_CYCLE_ON__: Lights are NOT IN cycle, bailing: '.$hgDeviceGroup->display_name,__METHOD__);
                        $return_status = HgGlozoneSmartTransition::RESULT_STATUS_NOT_INCYCLE;
                    }
                }
                else {
                    Yii::info('__SMARTTRANSITION_CYCLE_ON__: Lights are NOT ON in room: '.$hgDeviceGroup->display_name,__METHOD__);
                    $return_status = HgGlozoneSmartTransition::RESULT_STATUS_LIGHTS_NOT_ON;
                }
                break;
            case HgGlozoneTimeBlock::SMARTTRANSITION_IF_ON:
                //Check if any lights in the existing group are on
                $anyOn = false;
                // For HA, check if any lights in the group are on
                foreach ($hueLightsData as $lightData) {
                    if ($lightData['state']['on']) {
                        $anyOn = true;
                        break;
                    }
                }
                if ($anyOn) { //lights are on!
                    Yii::info('__SMARTTRANSITION_IF_ON__: Lights are ON: '.$hgDeviceGroup->display_name,__METHOD__);
                    Yii::info('__SMARTTRANSITION_IF_ON__: Executing: '.$hgDeviceGroup->display_name,__METHOD__);

                    $this->executeTurnGloOn($hgGlozoneSmartTransition);

                    $return_status = HgGlozoneSmartTransition::RESULT_STATUS_GLO_CHANGE;
                } else {
                    Yii::info('__SMARTTRANSITION_IF_ON__: Lights are NOT ON: '.$hgDeviceGroup->display_name,__METHOD__);
                    $return_status = HgGlozoneSmartTransition::RESULT_STATUS_LIGHTS_NOT_ON;
                }
                break;
            case HgGlozoneTimeBlock::SMARTTRANSITION_HARD_INVOKE:
                Yii::info('__SMARTTRANSITION_HARD_INVOKE__: Executing: '.$hgDeviceGroup->display_name,__METHOD__);
                $this->executeTurnGloOn($hgGlozoneSmartTransition);
                $return_status = HgGlozoneSmartTransition::RESULT_STATUS_GLO_CHANGE;
                break;
        }
        Yii::info('------------END----------'.$hgGlozoneSmartTransition->hgGlozoneTimeBlock->timeStartDefaultFormatted.'----TimeBlock:'.$hgGlozoneSmartTransition->hgGlozoneTimeBlock->display_name.'----Room:'.$hgGlozoneSmartTransition->hgDeviceGroup->display_name.'----Behavior'.$hgGlozoneSmartTransition->behavior_name.'----Glo:'.$hgGlozoneSmartTransition->hgGlozoneTimeBlock->defaultHgGlo->display_name,__METHOD__);
        return $return_status;
    }

    public function executeTurnGloOn(HgGlozoneSmartTransition $hgGlozoneSmartTransition)
    {
        $hgDeviceGroup = $hgGlozoneSmartTransition->hgDeviceGroup; //Current room
        $hgGlo = $hgGlozoneSmartTransition->hgGlozoneTimeBlock->defaultHgGlo;
        
        // Calculate transition time in seconds for HA
        $transitionTime = $hgGlozoneSmartTransition->hgGlozoneTimeBlock->smartTransition_duration_ms / 1000;

        if (!$this->_haComponent) {
            Yii::error('No Home Assistant component available', __METHOD__);
            return false;
        }

        // Get all light entities for this device group
        $lightEntityIds = [];
        $hgDeviceLights = HgDeviceLight::find()
            ->where(['primary_hg_device_group_id' => $hgDeviceGroup->id])
            ->all();
            
        foreach ($hgDeviceLights as $deviceLight) {
            if ($deviceLight->ha_device_id) {
                // Get light entities for this device
                $entities = $this->_haComponent->getDeviceLightEntities($deviceLight->ha_device_id);
                $lightEntityIds = array_merge($lightEntityIds, $entities);
            }
        }

        if (empty($lightEntityIds)) {
            Yii::warning('No light entities found for room: ' . $hgDeviceGroup->display_name, __METHOD__);
            return false;
        }

        try {
            if ($hgGlo->isOffGlo) {
                // Turn off lights
                $this->_haComponent->turnOffLights($lightEntityIds, $transitionTime);
            } else {
                // Turn on lights with glo settings
                $this->_haComponent->turnOnLightsWithGlo($lightEntityIds, $hgGlo, $transitionTime);
                Yii::info('Turned on lights with glo: ' . $hgGlo->display_name, __METHOD__);
            }
            return true;
        } catch (\Exception $e) {
            Yii::error('Failed to control lights: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * @param $previousHgGLoDevices
     * @param $hueLightsData
     * @param HgGlozoneSmartTransitionExecute|null $hgGlozoneSmartTransitionExecute if we want to record results into execute row
     * @return bool|int
     */
    public function compareInCycleColors($previousHgGLoDevices, $hueLightsData, HgGlozoneSmartTransitionExecute $hgGlozoneSmartTransitionExecute=NULL)
    {
        //get all the lights that are ON and loop through them
        $totalCount = 0;
        $trueCount = 0;
        $resultData=[];

        foreach ($hueLightsData as $device_light_id => $light_data) {
            if ($light_data['state']['on'] && $light_data['state']['reachable']) { //if light is on
                foreach ($previousHgGLoDevices as $hgGloDeviceLight) { //loop through the lights we have in the db
                    // For HA, compare by device light ID instead of hue_id
                    if ($hgGloDeviceLight->hgDeviceLight->isBulb && $hgGloDeviceLight->hgDeviceLight->id == $device_light_id) { //if they are the same light
                        $totalCount++;
                        $matched = false;
                        
                        //check for ct match
                        if ($light_data['state']['colormode'] == 'ct' && $hgGloDeviceLight->hueCt && $light_data['state']['ct'] !== null) {
                            $result_ct = HelperComponent::compareCtColors($hgGloDeviceLight->hueCt, $light_data['state']['ct']);
                            if ($result_ct) {
                                $trueCount++;
                                $matched = true;
                            }
                            Yii::info('__SMARTTRANSITION_CYCLE_ON_: Color Mode CT Compare Glo hg_device_light ('.$hgGloDeviceLight->hgDeviceLight->display_name.'):'.$hgGloDeviceLight->hueCt.' == State:'.$light_data['state']['ct'].' -- Result:'.($result_ct ? 'TRUE' : 'FALSE'),__METHOD__);
                            $resultData[$hgGloDeviceLight->hgDeviceLight->display_name] = 'CT Mode - Result:'.($result_ct?'TRUE':'FALSE').' - Current State:'.$light_data['state']['ct']. ' - Expected State:'.$hgGloDeviceLight->hueCt;
                        }
                        //check for xy match
                        else if ($light_data['state']['colormode'] == 'xy' && $hgGloDeviceLight->hueX && $hgGloDeviceLight->hueY && $light_data['state']['xy'] !== null) {
                            $result_xy = HelperComponent::compareXyColors([$hgGloDeviceLight->hueX,$hgGloDeviceLight->hueY], $light_data['state']['xy']);
                            if ($result_xy) {
                                $trueCount++;
                                $matched = true;
                            }
                            Yii::info('__SMARTTRANSITION_CYCLE_ON__: Color Mode XY Compare Glo ('.$hgGloDeviceLight->hgDeviceLight->display_name.'):['.$hgGloDeviceLight->hueX.','.$hgGloDeviceLight->hueY.'] == State:['.$light_data['state']['xy'][0].','.$light_data['state']['xy'][1].'] -- Result:'.($result_xy ? 'TRUE' : 'FALSE'),__METHOD__);
                            $resultData[$hgGloDeviceLight->hgDeviceLight->display_name] = 'XY Mode - Result:'.($result_xy?'TRUE':'FALSE').' - Current State:'.'['.$light_data['state']['xy'][0].','.$light_data['state']['xy'][1].']'. ' - Expected State:'.'['.$hgGloDeviceLight->hueX.','.$hgGloDeviceLight->hueY.']';
                        }
                        // If color modes don't match or no color data, it's not a match
                        else {
                            Yii::info('__SMARTTRANSITION_CYCLE_ON__: Color mode mismatch or missing data for light ('.$hgGloDeviceLight->hgDeviceLight->display_name.'). Light colormode: '.$light_data['state']['colormode'].', has CT: '.($hgGloDeviceLight->hueCt ? 'yes' : 'no').', has XY: '.($hgGloDeviceLight->hueX && $hgGloDeviceLight->hueY ? 'yes' : 'no'),__METHOD__);
                            $resultData[$hgGloDeviceLight->hgDeviceLight->display_name] = 'Color mode mismatch - Light mode: '.$light_data['state']['colormode'].', Expected: '.($hgGloDeviceLight->hueCt ? 'ct' : 'xy');
                        }
                    }
                }
            }
        }

        $resultData['result'] = 'trueCount:'.$trueCount. ' -- TotalCount:'.$totalCount;
        if ($hgGlozoneSmartTransitionExecute) {
            $hgGlozoneSmartTransitionExecute->appendJsonData($resultData);
        }

        Yii::info('__SMARTTRANSITION_CYCLE_ON_: trueCount:'.$trueCount. ' -- TotalCount:'.$totalCount,__METHOD__);

        if ($totalCount===0 && $trueCount===0) //return literal 0 if no lights are on
            return 0;

        return ($trueCount === $totalCount);

    }

    /**
     * If a sensor variable has been changed (e.g. inactivity time on a motion sensor)
     * loop through all the sensor's switch/motion map and update the variable in the hue hub
     * @param HgDeviceSensorVariable $hgDeviceSensorVariable
     */
    public function processHueRuleUpdatesBySensorVariable(HgDeviceSensorVariable $hgDeviceSensorVariable)
    {
        $hgDeviceSensor = $hgDeviceSensorVariable->hgDeviceSensor;
        $variable_name = $hgDeviceSensorVariable->variable_name;

        foreach ($hgDeviceSensor->hgHubActionTriggers as $hgHubActionTrigger) {
            if ($hgHubActionTrigger->getHasEventDataProperty($variable_name)) {
                try {
                    // TODO: Update for Home Assistant - Hue rule writing no longer needed
                    // AsyncWriteHueRulesByTriggerJob removed - no longer writing Hue rules
                } catch (\Throwable $t) {
                    \Yii::error($t->getMessage(),__METHOD__);
                    \Sentry\captureException($t);
                }
            }
        }
    }

    /**
     * If a sensor config variable has been changed, we must update the config.
     * e.g. light sensitivity for a sensor
     * @param HgDeviceSensorVariable $hgDeviceSensorVariable
     */
    public function processHueSensorConfigUpdatesBySensorVariable(HgDeviceSensorVariable $hgDeviceSensorVariable)
    {
        $hgDeviceSensor = $hgDeviceSensorVariable->hgDeviceSensor;
        $value = $hgDeviceSensorVariable->value;

        $data = null;
        switch ($hgDeviceSensorVariable->variable_name) {
            case HgDeviceSensorVariable::MOTION_DEFAULT_SENSITIVITY:
                $data = ['sensitivity'=>(int) $value];
                break;
            case HgDeviceSensorVariable::AMBIENT_DEFAULT_DARK_THRESHOLD:
                $data = ['tholddark'=>(int) $value];
                break;
        }

        // TODO: Update for Home Assistant - sensor configuration updates no longer needed
        // Previous logic pushed async Hue sensor config updates - replace with HA integration if needed
        if ($data) {
            // AsyncHueRequestJob removed - no longer updating Hue hub
        }
    }

    /**
     * Get the value in the hue
     * e.g. light sensitivity for a sensor
     * @param HgDeviceSensorVariable $hgDeviceSensorVariable
     */
    public function getHueValueBySensorVariable(HgDeviceSensorVariable $hgDeviceSensorVariable)
    {
        $hgDeviceSensor = $hgDeviceSensorVariable->hgDeviceSensor;

        switch ($hgDeviceSensorVariable->variable_name) {
            case HgDeviceSensorVariable::MOTION_DEFAULT_SENSITIVITY:
                $data = $this->_hgHub->getHueComponent()->v1GetRequest('sensors/'.$hgDeviceSensor->hue_id);
                return $data['config']['sensitivity'];
                break;
            case HgDeviceSensorVariable::AMBIENT_DEFAULT_DARK_THRESHOLD:
                $data = $this->_hgHub->getHueComponent()->v1GetRequest('sensors/'.$hgDeviceSensor->hue_id);
                return $data['config']['tholddark'];
                break;
            default:
                return NULL;
        }
    }
}
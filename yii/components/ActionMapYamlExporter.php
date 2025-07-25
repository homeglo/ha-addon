<?php

namespace app\components;

use app\models\HgHubActionMap;
use app\models\HgHubActionTemplate;
use app\models\HgHubActionTrigger;
use app\models\HgHubActionCondition;
use app\models\HgHubActionItem;
use Symfony\Component\Yaml\Yaml;
use yii\base\Component;

class ActionMapYamlExporter extends Component
{
    /**
     * Export an action map and all its related data to YAML
     * 
     * @param int $actionMapId
     * @return string|false YAML string or false if not found
     */
    public function exportActionMap($actionMapId)
    {
        $actionMap = HgHubActionMap::findOne($actionMapId);
        
        if (!$actionMap) {
            return false;
        }
        
        $data = $this->buildActionMapArray($actionMap);
        
        return Yaml::dump($data, 10, 2, Yaml::DUMP_OBJECT_AS_MAP);
    }
    
    /**
     * Build the complete action map array structure
     * 
     * @param HgHubActionMap $actionMap
     * @return array
     */
    public function buildActionMapArray($actionMap)
    {
        $data = [
            'action_map' => [
                'id' => $actionMap->id,
                'name' => $actionMap->name,
                'display_name' => $actionMap->display_name,
                'map_image_url' => $actionMap->map_image_url,
                'hg_home_id' => $actionMap->hg_home_id,
                'preserve_hue_buttons' => $actionMap->preserve_hue_buttons,
                'hg_product_sensor_map_type' => $actionMap->hg_product_sensor_map_type,
                'base_hg_hub_action_map_id' => $actionMap->base_hg_hub_action_map_id,
                'hg_status_id' => $actionMap->hg_status_id,
                'templates' => []
            ]
        ];
        
        // Get all templates for this action map
        $templates = $actionMap->getHgHubActionTemplates()->orderBy('id')->all();
        
        foreach ($templates as $template) {
            $data['action_map']['templates'][] = $this->buildTemplateArray($template);
        }
        
        return $data;
    }
    
    /**
     * Build template array with all its triggers
     * 
     * @param HgHubActionTemplate $template
     * @return array
     */
    protected function buildTemplateArray($template)
    {
        $templateData = [
            'id' => $template->id,
            'name' => $template->name,
            'display_name' => $template->display_name,
            'platform' => $template->platform,
            'multi_room' => $template->multi_room,
            'hg_product_sensor_type_name' => $template->hg_product_sensor_type_name,
            'hg_hub_id' => $template->hg_hub_id,
            'hg_version_id' => $template->hg_version_id,
            'hg_status_id' => $template->hg_status_id,
            'metadata' => $template->metadata,
            'triggers' => []
        ];
        
        // Get all triggers for this template
        $triggers = $template->getHgHubActionTriggers()->orderBy('rank, id')->all();
        
        foreach ($triggers as $trigger) {
            $templateData['triggers'][] = $this->buildTriggerArray($trigger);
        }
        
        return $this->removeNullValues($templateData);
    }
    
    /**
     * Build trigger array with conditions and actions
     * 
     * @param HgHubActionTrigger $trigger
     * @return array
     */
    protected function buildTriggerArray($trigger)
    {
        $triggerData = [
            'id' => $trigger->id,
            'name' => $trigger->name,
            'display_name' => $trigger->display_name,
            'source_name' => $trigger->source_name,
            'event_name' => $trigger->event_name,
            'event_data' => $trigger->event_data,
            'hg_hub_id' => $trigger->hg_hub_id,
            'ha_device_id' => $trigger->ha_device_id,
            'hg_device_sensor_id' => $trigger->hg_device_sensor_id,
            'hg_glozone_start_time_block_id' => $trigger->hg_glozone_start_time_block_id,
            'hg_glozone_end_time_block_id' => $trigger->hg_glozone_end_time_block_id,
            'hg_status_id' => $trigger->hg_status_id,
            'rank' => $trigger->rank,
            'metadata' => $trigger->metadata,
            'conditions' => [],
            'actions' => []
        ];
        
        // Get all conditions for this trigger
        $conditions = $trigger->getHgHubActionConditions()->orderBy('id')->all();
        
        foreach ($conditions as $condition) {
            $triggerData['conditions'][] = $this->buildConditionArray($condition);
        }
        
        // Get all action items for this trigger
        $actionItems = $trigger->getHgHubActionItems()->orderBy('id')->all();
        
        foreach ($actionItems as $actionItem) {
            $triggerData['actions'][] = $this->buildActionArray($actionItem);
        }
        
        return $this->removeNullValues($triggerData);
    }
    
    /**
     * Build condition array
     * 
     * @param HgHubActionCondition $condition
     * @return array
     */
    protected function buildConditionArray($condition)
    {
        $conditionData = [
            'id' => $condition->id,
            'name' => $condition->name,
            'display_name' => $condition->display_name,
            'property' => $condition->property,
            'operator' => $condition->operator,
            'value' => $condition->value,
            'hg_status_id' => $condition->hg_status_id,
            'metadata' => $condition->metadata
        ];
        
        return $this->removeNullValues($conditionData);
    }
    
    /**
     * Build action array
     * 
     * @param HgHubActionItem $actionItem
     * @return array
     */
    protected function buildActionArray($actionItem)
    {
        $actionData = [
            'id' => $actionItem->id,
            'entity' => $actionItem->entity,
            'operation_name' => $actionItem->operation_name,
            'operation_value_json' => $actionItem->operation_value_json,
            'operate_hg_device_light_group_id' => $actionItem->operate_hg_device_light_group_id,
            'hg_glo_id' => $actionItem->hg_glo_id,
            'display_name' => $actionItem->display_name,
            'override_hue_x' => $actionItem->override_hue_x,
            'override_hue_y' => $actionItem->override_hue_y,
            'override_bri_absolute' => $actionItem->override_bri_absolute,
            'override_bri_increment_percent' => $actionItem->override_bri_increment_percent,
            'override_transition_duration_ms' => $actionItem->override_transition_duration_ms,
            'override_transition_at_time' => $actionItem->override_transition_at_time,
            'metadata' => $actionItem->metadata
        ];
        
        return $this->removeNullValues($actionData);
    }
    
    /**
     * Remove null values from array for cleaner YAML
     * 
     * @param array $data
     * @return array
     */
    protected function removeNullValues($data)
    {
        return array_filter($data, function($value) {
            return $value !== null;
        });
    }
    
    /**
     * Get a simplified tree structure (useful for debugging or quick overview)
     * 
     * @param int $actionMapId
     * @return array|false
     */
    public function getSimplifiedTree($actionMapId)
    {
        $actionMap = HgHubActionMap::findOne($actionMapId);
        
        if (!$actionMap) {
            return false;
        }
        
        $tree = [
            'map' => $actionMap->display_name . ' (ID: ' . $actionMap->id . ')',
            'templates' => []
        ];
        
        foreach ($actionMap->hgHubActionTemplates as $template) {
            $templateNode = [
                'name' => $template->display_name . ' (ID: ' . $template->id . ')',
                'triggers' => []
            ];
            
            foreach ($template->hgHubActionTriggers as $trigger) {
                $triggerNode = [
                    'name' => $trigger->display_name . ' (ID: ' . $trigger->id . ')',
                    'event' => $trigger->event_name,
                    'conditions_count' => count($trigger->hgHubActionConditions),
                    'actions_count' => count($trigger->hgHubActionItems)
                ];
                
                $templateNode['triggers'][] = $triggerNode;
            }
            
            $tree['templates'][] = $templateNode;
        }
        
        return $tree;
    }
}
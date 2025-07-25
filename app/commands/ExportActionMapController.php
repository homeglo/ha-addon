<?php

namespace app\commands;

use yii\console\Controller;
use app\components\ActionMapYamlExporter;

/**
 * Export action maps to YAML format
 */
class ExportActionMapController extends Controller
{
    /**
     * Export an action map to YAML
     * 
     * @param int $id The action map ID
     */
    public function actionIndex($id)
    {
        $exporter = new ActionMapYamlExporter();
        
        // Get simplified tree first for overview
        $tree = $exporter->getSimplifiedTree($id);
        if ($tree === false) {
            echo "Action map with ID {$id} not found.\n";
            return 1;
        }
        
        echo "Exporting Action Map Tree:\n";
        echo "==========================\n";
        print_r($tree);
        echo "\n";
        
        // Export to YAML
        $yaml = $exporter->exportActionMap($id);
        
        // Save to file
        $filename = "action_map_{$id}_export.yaml";
        file_put_contents($filename, $yaml);
        
        echo "YAML exported to: {$filename}\n\n";
        echo "Preview (first 1000 characters):\n";
        echo "================================\n";
        echo substr($yaml, 0, 1000) . "...\n";
        
        return 0;
    }
    
    /**
     * Show simplified tree structure
     * 
     * @param int $id The action map ID
     */
    public function actionTree($id)
    {
        $exporter = new ActionMapYamlExporter();
        
        $tree = $exporter->getSimplifiedTree($id);
        if ($tree === false) {
            echo "Action map with ID {$id} not found.\n";
            return 1;
        }
        
        echo "Action Map Tree Structure:\n";
        echo "=========================\n";
        print_r($tree);
        
        return 0;
    }
}
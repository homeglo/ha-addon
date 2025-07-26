<?php

namespace app\helpers;

class AddonHelper
{
    /**
     * Get the addon version from config.yaml
     * @return string|null
     */
    public static function getAddonVersion()
    {
        // Try multiple locations
        $configPaths = [
            '/data/addon-config.yaml',  // Copied by init script
            '/app/homeglo/../config.yaml',  // Relative path that might work
        ];
        
        foreach ($configPaths as $configPath) {
            if (file_exists($configPath)) {
                try {
                    $content = file_get_contents($configPath);
                    // Simple regex to extract version from YAML
                    if (preg_match('/^version:\s*["\']?([^"\']+)["\']?$/m', $content, $matches)) {
                        return trim($matches[1]);
                    }
                } catch (\Exception $e) {
                    // Try next path
                    continue;
                }
            }
        }
        
        return null;
    }
}
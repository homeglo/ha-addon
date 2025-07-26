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
        $configPath = '/app/config.yaml';
        
        if (file_exists($configPath)) {
            try {
                $content = file_get_contents($configPath);
                // Simple regex to extract version from YAML
                if (preg_match('/^version:\s*["\']?([^"\']+)["\']?$/m', $content, $matches)) {
                    return trim($matches[1]);
                }
            } catch (\Exception $e) {
                // If we can't read the file, return null
                return null;
            }
        }
        
        return null;
    }
}
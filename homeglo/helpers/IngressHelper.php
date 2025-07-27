<?php

namespace app\helpers;

use Yii;

class IngressHelper
{
    /**
     * Check if running under Home Assistant ingress
     * @return bool
     */
    public static function isIngressMode()
    {
        return !empty($_SERVER['HTTP_X_INGRESS_PATH']);
    }
    
    /**
     * Get the ingress path prefix
     * @return string
     */
    public static function getIngressPath()
    {
        return $_SERVER['HTTP_X_INGRESS_PATH'] ?? '';
    }
    
    /**
     * Create URL that works in both ingress and non-ingress modes
     * @param string|array $route
     * @param bool $scheme
     * @return string
     */
    public static function createUrl($route, $scheme = false)
    {
        $url = Yii::$app->urlManager->createUrl($route, $scheme);
        
        // In ingress mode, prepend the ingress path
        if (self::isIngressMode()) {
            $ingressPath = self::getIngressPath();
            // Remove leading slash from URL if present
            $url = ltrim($url, '/');
            return $ingressPath . '/' . $url;
        }
        
        return $url;
    }
    
    /**
     * Create absolute URL that works in both modes
     * @param string|array $route
     * @param string|null $scheme
     * @return string
     */
    public static function createAbsoluteUrl($route, $scheme = null)
    {
        $url = Yii::$app->urlManager->createAbsoluteUrl($route, $scheme);
        
        // In ingress mode, we need to use the proper host
        if (self::isIngressMode()) {
            // Parse the URL
            $parsed = parse_url($url);
            $path = $parsed['path'] ?? '/';
            
            // Build the correct URL with ingress path
            $host = Yii::$app->request->hostInfo;
            $ingressPath = self::getIngressPath();
            
            return $host . $ingressPath . $path;
        }
        
        return $url;
    }
    
    /**
     * Get base URL for assets
     * @return string
     */
    public static function getBaseUrl()
    {
        if (self::isIngressMode()) {
            return self::getIngressPath();
        }
        return Yii::$app->request->baseUrl;
    }
    
    /**
     * Check if we have Home Assistant connection
     * @return bool
     */
    public static function hasHomeAssistantConnection()
    {
        // Check if we have supervisor token
        return getenv('SUPERVISOR_TOKEN') || (getenv('HA_TOKEN'));
    }
    
    /**
     * Get display mode
     * @return string 'ingress', 'standalone-ha', or 'standalone'
     */
    public static function getDisplayMode()
    {
        if (self::isIngressMode()) {
            return 'ingress';
        } elseif (self::hasHomeAssistantConnection()) {
            return 'standalone-ha';
        } else {
            return 'standalone';
        }
    }
}
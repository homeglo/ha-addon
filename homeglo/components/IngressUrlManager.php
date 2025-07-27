<?php

namespace app\components;

use yii\web\UrlManager;
use app\helpers\IngressHelper;

/**
 * Custom UrlManager that automatically handles Home Assistant ingress URLs
 */
class IngressUrlManager extends UrlManager
{
    /**
     * Creates a URL using the given route and query parameters.
     * Automatically handles ingress path prefixing.
     * 
     * @param string|array $params use a string to represent a route (e.g. `site/index`),
     * or an array to represent a route with query parameters (e.g. `['site/index', 'param1' => 'value1']`).
     * @param string|null $scheme the scheme to use for the URL
     * @return string the created URL
     */
    public function createUrl($params, $scheme = null)
    {
        // If scheme is provided, delegate to createAbsoluteUrl
        if ($scheme !== null) {
            return $this->createAbsoluteUrl($params, $scheme);
        }
        
        // Get the base URL from parent
        $url = parent::createUrl($params);
        
        // In ingress mode, prepend the ingress path
        if (IngressHelper::isIngressMode()) {
            $ingressPath = IngressHelper::getIngressPath();
            // Remove leading slash from URL if present
            $url = ltrim($url, '/');
            return $ingressPath . '/' . $url;
        }
        
        return $url;
    }
    
    /**
     * Creates an absolute URL using the given route and query parameters.
     * Automatically handles ingress path prefixing.
     * 
     * @param string|array $params use a string to represent a route (e.g. `site/index`),
     * or an array to represent a route with query parameters (e.g. `['site/index', 'param1' => 'value1']`).
     * @param string|null $scheme the scheme to use for the URL
     * @return string the created absolute URL
     */
    public function createAbsoluteUrl($params, $scheme = null)
    {
        $url = parent::createAbsoluteUrl($params, $scheme);
        
        // In ingress mode, we need to use the proper host
        if (IngressHelper::isIngressMode()) {
            // Parse the URL
            $parsed = parse_url($url);
            $path = $parsed['path'] ?? '/';
            
            // Build the correct URL with ingress path
            $host = \Yii::$app->request->hostInfo;
            $ingressPath = IngressHelper::getIngressPath();
            
            return $host . $ingressPath . $path . (isset($parsed['query']) ? '?' . $parsed['query'] : '');
        }
        
        return $url;
    }
}
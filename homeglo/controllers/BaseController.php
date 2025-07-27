<?php

namespace app\controllers;

use yii\web\Controller;
use yii\helpers\Url;

/**
 * Base controller that handles ingress URLs in redirects
 */
class BaseController extends Controller
{
    /**
     * Redirects the browser to the specified URL.
     * Uses Url::to() which will use our custom IngressUrlManager
     * 
     * @param string|array $url the URL to be redirected to
     * @param int $statusCode the HTTP status code. Defaults to 302
     * @return \yii\web\Response the current response object
     */
    public function redirect($url, $statusCode = 302)
    {
        if (is_array($url)) {
            // Use Url::to() which will use our custom IngressUrlManager
            $url = Url::to($url);
        }
        
        return parent::redirect($url, $statusCode);
    }
}
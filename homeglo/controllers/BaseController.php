<?php

namespace app\controllers;

use yii\web\Controller;
use app\helpers\IngressHelper;

/**
 * Base controller that handles ingress URLs in redirects
 */
class BaseController extends Controller
{
    /**
     * Redirects the browser to the specified URL.
     * Automatically handles ingress paths.
     * 
     * @param string|array $url the URL to be redirected to
     * @param int $statusCode the HTTP status code. Defaults to 302
     * @return \yii\web\Response the current response object
     */
    public function redirect($url, $statusCode = 302)
    {
        if (is_array($url)) {
            $url = IngressHelper::createUrl($url);
        }
        
        return parent::redirect($url, $statusCode);
    }
}
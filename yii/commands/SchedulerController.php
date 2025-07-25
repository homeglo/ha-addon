<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\components\AirtableComponent;
use app\components\HelperComponent;
use app\components\HgEngineComponent;
use app\components\HueComponent;
use app\models\HgGlozoneSmartTransition;
use app\models\HgGlozoneSmartTransitionExecute;
use app\models\HgHome;
use app\models\HgHub;
use app\models\HgHubActionTrigger;
use app\models\HgStatus;
use app\models\HgVersion;
use NXP\MathExecutor;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class SchedulerController extends Controller
{
    /**
     * Clean up the smart transition execute rows. These rows build up over time. we only want to keep (7)
     * days worth of history
     */
    public function actionCleanupTransitionExecuteRows()
    {
        $daysAgo = time()-86400*7; // 7 days history
        $rows = HgGlozoneSmartTransitionExecute::deleteAll(['<','created_at',$daysAgo]);
        \Yii::info('Cleared HgGlozoneSmartTransitionExecute rows:'.$rows,__METHOD__);
    }

    public function actionFunAutomations()
    {
        $url = 'https://pro-api.coinmarketcap.com/v2/cryptocurrency/quotes/latest?symbol=btc';

        $headers = [
            'Accepts: application/json',
            'X-CMC_PRO_API_KEY: a1dc6f48-b08b-449b-a8c7-1a35298c592e'
        ];

        $request = $url;


        $curl = curl_init(); // Get cURL resource
        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => $request,            // set the request URL
            CURLOPT_HTTPHEADER => $headers,     // set the headers
            CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
        ));

        $response = curl_exec($curl); // Send the request, save the response
        $response = json_decode($response,true);
        $quote = $response['data']['BTC'][0]['quote']['USD'];
        if ($quote['percent_change_1h'] >= 1) {
            //turn on lights in a hub
        }
    }
}

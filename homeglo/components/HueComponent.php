<?php

namespace app\components;

use app\exceptions\HueApiException;
use app\models\HgHub;
use Yii;
use yii\base\Component;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

class HueComponent extends Component {
    private string $_applicationKey;
    private string $_ipAddress;
    private string $_bearerToken;

    function __construct($applicationKey, $bearerToken, $ipAddress='api.meethue.com/route')
    {
        $this->_ipAddress = $ipAddress;
        $this->_applicationKey = $applicationKey;
        $this->_bearerToken = $bearerToken;
        parent::__construct();
    }

    public function v1GetRequest($resource)
    {
        $client = new \GuzzleHttp\Client(['base_uri' => 'https://'.$this->_ipAddress.'/api/'.$this->_applicationKey.'/']);
        $response = $client->request('GET', $resource, [
            'verify'=>false,
            //'debug'=>true,
            'headers'=>['Authorization'=>'Bearer '.$this->_bearerToken]
        ]);
        Yii::info('Get Response:'.json_encode(json_decode($response->getBody(),TRUE)),__METHOD__.'/'.$resource);
        return json_decode($response->getBody(),TRUE);
    }

    public function v2GetRequest($resource)
    {
        $client = new \GuzzleHttp\Client(['base_uri' => 'https://'.$this->_ipAddress.'/clip/v2/resource/']);
        $response = $client->request('GET', $resource, [
            'headers' => ['hue-application-key' => $this->_applicationKey],
            'verify'=>false
        ]);
        return json_decode($response->getBody(),TRUE)['data'];
    }

    public function v1PostRequest($resource, $data)
    {
        Yii::info('Post Request:'.json_encode($data),__METHOD__.'/'.$resource);
        $client = new \GuzzleHttp\Client(['base_uri' => 'https://'.$this->_ipAddress.'/api/'.$this->_applicationKey.'/']);
        $response = $client->request('POST', $resource, [
            //'debug'=>true,
            'body'=>json_encode($data),
            'headers'=>[
                'Authorization'=>'Bearer '.$this->_bearerToken,
                'content-type'=>'application/json']
        ]);
        Yii::info('Post Response:'.json_encode(json_decode($response->getBody(),TRUE)),__METHOD__.'/'.$resource);

        $responseArray = json_decode($response->getBody(),TRUE);

        if (static::isResponseError($responseArray)) {
            throw new HueApiException($responseArray[0]['error']['description']);
        }

        return $responseArray;

    }

    public function v1PutRequest($resource, $data)
    {
        Yii::info('Put Request:'.json_encode($data),__METHOD__.'/'.$resource);
        $client = new \GuzzleHttp\Client(['base_uri' => 'https://'.$this->_ipAddress.'/api/'.$this->_applicationKey.'/']);
        $response = $client->request('PUT', $resource, [
            //'debug'=>true,
            'body'=>json_encode($data),
            'headers'=>[
                'Authorization'=>'Bearer '.$this->_bearerToken,
                'content-type'=>'application/json']
        ]);
        Yii::info('Put Response:'.json_encode(json_decode($response->getBody(),TRUE)),__METHOD__.'/'.$resource);

        $responseArray = json_decode($response->getBody(),TRUE);

        if (static::isResponseError($responseArray)) {
            throw new HueApiException($responseArray[0]['error']['description']);
        }

        return $responseArray;

    }

    public function v1DeleteRequest($resource)
    {
        Yii::info('Delete Request',__METHOD__.'/'.$resource);
        $client = new \GuzzleHttp\Client(['base_uri' => 'https://'.$this->_ipAddress.'/api/'.$this->_applicationKey.'/']);
        $response = $client->request('DELETE', $resource,
        [
            'headers'=>[
                //'debug'=>true,
                'Authorization'=>'Bearer '.$this->_bearerToken
            ]
        ]);
        Yii::info('Delete Response:'.json_encode(json_decode($response->getBody(),TRUE)),__METHOD__.'/'.$resource);

        $responseArray = json_decode($response->getBody(),TRUE);

        if (static::isResponseError($responseArray)) {
            throw new HueApiException($responseArray[0]['error']['description']);
        }

        return $responseArray;

    }

    /**
     * @param array $response
     * @return bool
     *
     * Return if there is an error in processing a hue api call
     */
    public static function isResponseError(array $response)
    {
        if (array_key_exists('error',@$response[0]))
            return true;
        else
            return false;
    }

    public function v2PostRequest($resource, $data)
    {

    }

    /**
     * Get rules that are tied to a switch
     * @param int $switchId
     * @return array
     */
    public function getSwitchRules(int $switchId, int $variable_sensor_id=NULL): array
    {
        $rules = $this->v1GetRequest('rules');

        $switchRules = [];
        foreach ($rules as $id => $r) {
            foreach ($r as $key => $value) {
                if ($key == 'conditions') {
                    foreach ($value as $condition) {
                        if (stripos($condition['address'],'/sensors/'.$switchId.'/state') !== FALSE) {
                            $switchRules[$id] = $r;
                        }

                        if (stripos($condition['address'],'/sensors/'.$variable_sensor_id.'/state') !== FALSE) {
                            $switchRules[$id] = $r;
                        }
                    }
                }
            }
        }
        return $switchRules;
    }

    public function deleteSwitchRules(int $switchId)
    {
        foreach (static::getSwitchRules($switchId) as $id => $rule) {
            $this->v1DeleteRequest('rules/'.$id);
        }
    }

    /**
     * @param array
     */
    public function createRuleBasedOnRule($rule)
    {
        unset($rule['owner']);
        unset($rule['created']);
        unset($rule['lasttriggered']);
        unset($rule['timestriggered']);
        $response = $this->v1PostRequest('rules',$rule);
    }

    /**
     * @param $code
     * @return mixed
     * ['access_token'=>,
     * 'expires_in'=>
     * 'refresh_token'=>,
     * 'token_type'=>]
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getBearerAccessTokens($code)
    {
        $client = new \GuzzleHttp\Client(['base_uri' => 'https://api.meethue.com/v2/oauth2/']);
        $response = $client->request('POST', 'token', [
            //'verify'=>false,
            'form_params'=>[
                'grant_type'=>'authorization_code',
                'code'=>$code
            ],
            'headers'=>[
                'Authorization'=>'Basic '.base64_encode($_ENV['HUE_CLIENT_ID'].':'.$_ENV['HUE_CLIENT_SECRET']),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ]);

        return json_decode($response->getBody(),TRUE);
    }

    public static function getHueApplicationKey($bearer_token)
    {
        $client = new \GuzzleHttp\Client(['base_uri' => 'https://api.meethue.com/route/api/0/']);
        $response = $client->request('PUT', 'config', [
            'body'=>json_encode(['linkbutton'=>true]),
            'headers'=>[
                'Authorization'=>'Bearer '.$bearer_token,
                'Content-Type' => 'application/json'
            ]
        ]);

        $client = new \GuzzleHttp\Client(['base_uri' => 'https://api.meethue.com/route/']);
        $response = $client->request('POST', 'api', [
            'body'=>json_encode(['devicetype'=>'HomeGlo']),
            'headers'=>[
                'Authorization'=>'Bearer '.$bearer_token,
                'Content-Type' => 'application/json'
            ]
        ]);

        $hueResponse = json_decode($response->getBody(),TRUE);
        if (isset($hueResponse[0]['success']['username'])) {
            return $hueResponse[0]['success']['username'];
        } else {
            throw new \Exception('Unable to create hue user!');
        }
    }

    /**
     * @param HgHub $hubRecord
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function refreshToken(HgHub $hubRecord)
    {
        $client = new \GuzzleHttp\Client(['base_uri' => 'https://api.meethue.com/v2/oauth2/']);
        $response = $client->request('POST', 'token', [
            'form_params'=>[
                'grant_type'=>'refresh_token',
                'client_id'=>$_ENV['HUE_CLIENT_ID'],
                'client_secret'=>$_ENV['HUE_CLIENT_SECRET'],
                'refresh_token'=>$hubRecord->refresh_token
            ]
        ]);

        $tokens = json_decode($response->getBody(),TRUE);

        $hubRecord->bearer_token = $tokens['access_token'];
        $hubRecord->refresh_token = $tokens['refresh_token'];
        $hubRecord->token_expires_at = $tokens['expires_in'] + time();

        if ($hubRecord->save()) {
            Yii::info('Token refreshed! Hub:'.$hubRecord->display_name,__METHOD__);
            return $hubRecord;
        }

        throw new \Exception('Unable to save hub record on token refresh!');
    }

    public function turnOnAllLights($ct=100,$bri=100)
    {
        $this->v1PutRequest('groups/0/action',[
            'on'=>true,
            'bri'=>$bri,
            'ct'=>$ct
        ]);
    }

    public function turnOffAllLights()
    {
        $this->v1PutRequest('groups/0/action',[
            'on'=>false
        ]);
    }
}
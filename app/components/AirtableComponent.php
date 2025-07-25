<?php

namespace app\components;

use TANIOS\Airtable\Airtable;
use Yii;
use yii\base\Component;

class AirtableComponent extends Component {

    public static function instantiate()
    {
        $airtable = new Airtable(array(
            'api_key' => $_ENV['AIRTABLE_API_KEY'],
            'base'    => $_ENV['AIRTABLE_BASE_ID']
        ));

        return $airtable;
    }

    public static function getNormalizedHubs($home_name=NULL)
    {
        $airtable = AirtableComponent::instantiate();
        $request = $airtable->getContent('Hub',false,['Home']);
        $response = $request->getResponse();
        $allHubs = [];
        $home_hubs = [];
        foreach ($response['records'] as $object) {
            $tempArray = [
                'id'=>$object->id,
                'name'=>$object->fields->Name,
                'hub_id'=>$object->fields->ID,
                'home'=>$object->fields->Home[0]->Home,
                'access_token'=>@$object->fields->{"Access Token"},
                'bearer_token'=>@$object->fields->{"Bearer Token"},
                'refresh_token'=>@$object->fields->{"Refresh Token"},
                'token_expires_at'=>@$object->fields->{"Token Expires At"},
            ];

            $allHubs[] = $tempArray;


            if ($home_name && ($home_name == $object->fields->Home[0]->Home)) {
                $home_hubs[$tempArray['hub_id']] = $tempArray;
            }
        }

        if ($home_name) {
            if (empty($home_hubs))
                return [];
            else
                return $home_hubs;
        }

        return $allHubs;
    }

    public static function getNormalizedHomes($homeglo_id=NULL)
    {
        $airtable = AirtableComponent::instantiate();
        $request = $airtable->getContent('Home',false,['Hub']);
        $response = $request->getResponse();
        $array = [];

        foreach ($response['records'] as $object) {
            $tempArray = [
                'id'=>$object->id,
                'home'=>$object->fields->Home,
                'homeglo_id'=>$object->fields->ID[0],
                'nickname'=>$object->fields->Nickname[0],
                'client'=>$object->fields->Client[0],
            ];
            $array[] = $tempArray;

            if ($homeglo_id && ($tempArray['homeglo_id'] == $homeglo_id)) {
                return $tempArray;
            }
        }

        return $array;
    }

}


/*
 *
stdClass Object
(
    [id] => recLpscorQtGcmXs8
    [createdTime] => 2022-07-11T19:19:18.000Z
    [fields] => stdClass Object
        (
            [Type] => Array
                (
                    [0] => reclK0Q23EiDN1bBF
                )

            [Name] => 1-hub
            [Home] => Array
                (
                    [0] => stdClass Object
                        (
                            [V] => Array
                                (
                                    [0] => recGDXzKf0KdInMDt
                                )

                            [Glo] => Day, Afternoon, Sunset, Evening, Relax, Bedtime, Last call, Morning, Bright
                            [Home feed] => Array
                                (
                                    [0] => recYX5gNVFxZ9tcW1
                                )

                            [Room] => Array
                                (
                                    [0] => reccnKlwdLFNyLPRQ
                                    [1] => rec3Qh5QIkBaAmYYh
                                    [2] => recmlB1m6fIOJaGIb
                                    [3] => recSNXASubD6f0pV0
                                    [4] => recNZASSWerpwJYyv
                                    [5] => recdWAzbD3sr6JqQk
                                )

                            [Glo zone] => Array
                                (
                                    [0] => recM6NMe9n1pA6ydL
                                )

                            [Hub] => Array
                                (
                                    [0] => recLpscorQtGcmXs8
                                )

                            [Home] => HomeGlo (HG)
                            [ID] => Array
                                (
                                    [0] => 2
                                )

                            [Nickname] => Array
                                (
                                    [0] => HomeGlo
                                )

                            [Client] => Array
                                (
                                    [0] => HomeGlo
                                )

                            [id] => recFtscpKJRqRQqoT
                        )

                )

            [Hub] => HomeGlo (HG) 1-hub
            [Home V] => Array
                (
                    [0] => recGDXzKf0KdInMDt
                )

        )

)
stdClass Object
(
    [id] => reckRmOKfaOggFssF
    [createdTime] => 2022-07-13T20:25:25.000Z
    [fields] => stdClass Object
        (
            [Type] => Array
                (
                    [0] => reclK0Q23EiDN1bBF
                )

            [Name] => 1-hub
            [Home] => Array
                (
                    [0] => stdClass Object
                        (
                            [V] => Array
                                (
                                    [0] => recGDXzKf0KdInMDt
                                )

                            [Home feed] => Array
                                (
                                    [0] => recN8rlvmSadFQOE3
                                )

                            [Room] => Array
                                (
                                    [0] => recDw6LXJWIU5Q7F8
                                    [1] => recqr47IvMO1GFoPE
                                    [2] => reczdSQnvqwtmYAoh
                                    [3] => recjm1z3lJRkms9ox
                                )

                            [Glo zone] => Array
                                (
                                    [0] => recgSb1maP8p9hoho
                                )

                            [Hub] => Array
                                (
                                    [0] => reckRmOKfaOggFssF
                                )

                            [Home] => Hue 644 (Ron)
                            [ID] => Array
                                (
                                    [0] => 3
                                )

                            [Nickname] => Array
                                (
                                    [0] => Hue 644
                                )

                            [Client] => Array
                                (
                                    [0] => Ron Weisbein
                                )

                            [id] => recYuDufP8TFGrB7y
                        )

                )

            [Device] => Array
                (
                    [0] => recQY47RwUs56Vc9h
                    [1] => recBMzY2msYkwfUkg
                    [2] => recIQwJYqN2YqQdDV
                    [3] => recfyPsenVYSr5QDl
                    [4] => recRoXffDvBxbaQIH
                    [5] => recjuiaulL0KjV1b8
                    [6] => recEGe2lTRqLknZde
                    [7] => recMBLaqyPdIIiBEh
                    [8] => recNfqHDZmHuFqCtb
                    [9] => recW3CzQnHvADsLRY
                    [10] => rec30wxVUEfizuR5r
                    [11] => recKqVVMzADcANemP
                    [12] => recbRmyfA4k3dRVR2
                    [13] => rechbyKeLhFiL6CzX
                    [14] => rec7DXVDlshbiBMnl
                    [15] => rec2UoG2dOeODgQsO
                    [16] => recLgrxIss1eaFL1G
                    [17] => recO6xDic97RglTDx
                    [18] => recZzKDVNBDqT0oYi
                )

            [Hub] => Hue 644 (Ron) 1-hub
            [Home V] => Array
                (
                    [0] => recGDXzKf0KdInMDt
                )

        )

)

 */
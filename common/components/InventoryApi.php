<?php

namespace common\components;

use Yii;

/*
  @Usage :: Yii::$app->inventoryApi->functionName(param1,param2..)
 */

Class InventoryApi extends \yii\base\Component {

    public static function getUrl($route) {
        return Yii::$app->params['INVENTORY_API_URL'] . $route;
    }

    /**
     * Autrher: Arun Yadav
     * Date: 02-May-2018
     * Desc: Common function for call API
     */
    public static function getApiResponse($url, $data, $requestType = 'POST', $headers = array(), $xmldata = false) {
        return false;
        $curl = curl_init();
        switch ($requestType) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        if ($xmldata == TRUE) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
        }
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        // Optional Authentication:
        // curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    /**
     * Autrher: Arun Yadav
     * Date: 02-May-2018
     * Desc: Common Inventory Api function for getting free port using category
     */
    public static function getPortDetail($input) {
        $response = '';
        if (isset($input['hostname']) && isset($input['category'])) {
            $url = self::getUrl('getdevicedata');
            $request = Array(
                "data" => Array(
                    "hostname" => $input['hostname'],
                    "category" => $input['category']
                )
            );
            $request = json_encode($request);
            $response = self::getApiResponse($url, $request);
            $response = json_decode($response, true);
            if (isset($response['data'])) {
                $response = $response['data'][$input['hostname']];
            } else {
                return false;
            }
        }
        return $response;
    }

    /**
     * Autrher: Arun Yadav
     * Date: 02-May-2018
     * Desc: Common Inventory Api function for getting free port using category
     */
    public static function getUsedPort($input) {
        $response = '';
        if (isset($input['hostname']) && isset($input['category'])) {
            $url = self::getUrl('getdevicedata');
            $request = Array(
                "data" => Array(
                    "hostname" => $input['hostname'],
                    "category" => $input['category']
                )
            );
            $request = json_encode($request);
            $response = self::getApiResponse($url, $request);
            $response = json_decode($response, true);
            if (isset($response['data'])) {
                $response = $response['data'][$input['hostname']];
            } else {
                return false;
            }
        }
        return $response;
    }

    /**
     * Autrher: Arun Yadav
     * Date: 02-May-2018
     * Desc: Common Inventory Api function for adding new device
     */
    public static function addNewDevice($input) {
        $response = false;
        if (isset($input['hostname']) && isset($input['sapid']) && isset($input['loopback0']) && isset($input['loopback999']) && isset($input['routertype'])) {
            $url = self::getUrl('adddevice');
            $request = Array(
                "data" => Array(
                    "hostname" => $input['hostname'],
                    "sapid" => $input['sapid'],
                    "loopback0" => $input['loopback0'],
                    "loopback999" => $input['loopback999'],
                    "routertype" => $input['routertype']
                )
            );
            $request = json_encode($request);
            $response = self::getApiResponse($url, $request);
            $response = json_decode($response, true);
            if (isset($response['data'])) {
                $response = $response['data'];
            } else {
                return false;
            }
        }
        return $response;
    }

    /**
     * Autrher: Arun Yadav
     * Date: 02-May-2018
     * Desc: Common Inventory Api function for getting Command data
     */
    public static function getCommandData($input) {
        $response = false;
        if (isset($input['hostname']) && isset($input['commandcode'])) {
            $url = self::getUrl('getcommanddata');
            $request = Array(
                "data" => Array(
                    "hostname" => $input['hostname'],
                    "commandcode" => $input['commandcode']
                )
            );
            $request = json_encode($request);
            $response = self::getApiResponse($url, $request);
            $response = json_decode($response, true);
            if (isset($response['data'])) {
                $response = $response['data'];
            } else {
                return false;
            }
        }
        return $response;
    }

    /**
     * Autrher: Arun Yadav
     * Date: 02-May-2018
     * Desc: Common Inventory Api function for getting near br device using lat long
     */
    public static function getNearByDevicesUsingLatLong($input) {
        $response = false;
        if (isset($input['latitude']) && isset($input['longitude'])) {
            $url = self::getUrl('getnearbydevices');
            $request = [
                "data" => [
                    "latitude" => $input['latitude'],
                    "longitude" => $input['longitude'],
                    "radius" => isset($input["radius"]) ? $input["radius"] : 50
                ]
            ];
            $request = json_encode($request);
            $response = self::getApiResponse($url, $request);
            $response = json_decode($response, true);
            if (isset($response['data'])) {
                $response = $response['data'];
            } else {
                return false;
            }
        }
        return $response;
    }

    /**
     * Autrher: Arun Yadav
     * Date: 02-May-2018
     * Desc: Common Inventory Api function for getting near by device using pin code
     */
    public static function getNearByDevicesUsingPincode($input) {
        $response = false;
        if (isset($input['pincode']) && isset($input['device_type'])) {
            $url = self::getUrl('getnearbydevices');
            $request = Array(
                "data" => Array(
                    "pincode" => $input['pincode'],
                    "device_type" => $input['device_type']
                )
            );
            $request = json_encode($request);
            $response = self::getApiResponse($url, $request);
            $response = json_decode($response, true);
            if (isset($response['data'])) {
                $response = $response['data'];
            } else {
                return false;
            }
        }
        return $response;
    }

    /**
     * Autrher: Arun Yadav
     * Date: 02-May-2018
     * Desc: Common Inventory Api function for getting device details using neid
     */
    public static function getDeviceDetails($input) {
        $response = false;
        if (isset($input['neid'])) {
            $url = self::getUrl('getdevicedetails');
            $request = Array(
                "data" => Array(
                    "neid" => $input['neid']
                )
            );
            $request = json_encode($request);
            $response = self::getApiResponse($url, $request);
            $response = json_decode($response, true);
            if (isset($response['data'])) {
                $response = $response['data'];
            } else {
                return false;
            }
        }
        return $response;
    }
    public static function getDeviceDetailsById($input) {
        $response = false;
        if (isset($input['id'])) {
            $url = self::getUrl('getdevicedetailsbyid');
            $request = Array(
                "data" => Array(
                    "id" => $input['id']
                )
            );
            $request = json_encode($request);
            $response = self::getApiResponse($url, $request);
            $response = json_decode($response, true);
            if (isset($response['data'])) {
                $response = $response['data'];
            } else {
                return false;
            }
        }
        return $response;
    }

    public static function getExclusionVlan($input) {
        $response = false;
        if (isset($input['hostname']) && isset($input['neighbors_hostname'])) {
            $url = self::getUrl('getexclusionvlan');
            $request = Array(
                "data" => Array(
                    "hostname" => $input['hostname'],
                    "neighbors_hostname" => $input['neighbors_hostname']
                )
            );
            $request = json_encode($request);
            $response = self::getApiResponse($url, $request);
            $response = json_decode($response, true);
            if (isset($response['data']["devices"]["exclusion_vlan"])) {
                $response = $response['data']["devices"]["exclusion_vlan"];
            } else {
                return false;
            }
        }
        return $response;
    }
	
	 public static function getInclusionVlan($input) {
        $response = false;
        if (isset($input['hostname']) && isset($input['neighbors_hostname'])) {
            $url = self::getUrl('getinclusionvlans');
            $request = Array(
                "data" => Array(
                    "hostname" => $input['hostname'],
                    "neighbors_hostname" => $input['neighbors_hostname']
                )
            );
            $request = json_encode($request);
            $response = self::getApiResponse($url, $request);
            $response = json_decode($response, true);
            if (isset($response['data']["inclusion_vlans"])) {
                $response = $response['data']["inclusion_vlans"];
            } else {
                return false;
            }
        }
        return $response;
    }

    public static function getLocalInterface($input) {
        $response = false;
        if (isset($input['hostname']) && isset($input['device_hostname'])) {
            $url = self::getUrl('getlocalinterface');
            $request = Array(
                "data" => Array(
                    "hostname" => $input['hostname'],
                    "device_hostname" => $input['device_hostname']
                )
            );
            $request = json_encode($request);
	        $response = self::getApiResponse($url, $request);
            $response = json_decode($response, true);
            if (isset($response['data']["devices"]["intf_facing_ecr"][$input['hostname']])) {
                $response = $response['data']["devices"]["intf_facing_ecr"][$input['hostname']];
            } else {
                return false;
            }
        }
        return $response;
    }

    public static function getServiceInstance($input) {
        $response = false;
        if (isset($input['hostname']) && isset($input['interface']) && isset($input['commandcode'])) {
            $url = self::getUrl('getserviceinstencvlan');
            $request = Array(
                "data" => Array(
                    "hostname" => $input['hostname'],
                    "interface" => $input['interface'],
                    "commandcode" => $input['commandcode']
                )
            );
            $request = json_encode($request);
            $response = self::getApiResponse($url, $request);
            $response = json_decode($response, true);
            if (isset($response['data']["service_instance"])) {
               return $response = $response['data']["service_instance"];
				//pe($response);
            } else {
                return false;
            }
        }
        return $response;
    }

    public static function getEncapsulationVlan($input) {
        $response = false;
        if (isset($input['hostname']) && isset($input['interface']) && isset($input['commandcode'])) {
            $url = self::getUrl('getserviceinstencvlan');
            $request = Array(
                "data" => Array(
                    "hostname" => $input['hostname'],
                    "interface" => $input['interface'],
                    "commandcode" => $input['commandcode']
                )
            );
            $request = json_encode($request);
            $response = self::getApiResponse($url, $request);
            $response = json_decode($response, true);
            if (isset($response['data'])) {
                $response = $response['data']['encapsulation_vlan'];
            } else {
                return false;
            }
        }
        return $response;
    }

    public static function getTopology($input) {
        $response = false;
        if (isset($input['hostname'])) {
            $url = self::getUrl('gettopology');
            $request = Array(
                "data" => Array(
                    "hostname" => $input['hostname']
                )
            );
            $request = json_encode($request);
			$response = self::getApiResponse($url, $request);
			$response = json_decode($response, true);
            if (isset($response['data'])) {
               return $response = $response['data'];
            } else {
                return false;
            }
        }
    }

}

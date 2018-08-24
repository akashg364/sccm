<?php

namespace backend\models;

use Yii;
use backend\models\Ipv6Subnetting;
use backend\models\Ipv6Pool;
use common\components\IpAddressHelper;
use yii\db\Query;

/**
 * This is the model class for table "tbl_ipv6_pool_assignment".
 *
 * @property int $id
 * @property int $pool_id
 * @property int $service_instance_id
 * @property string $subnet
 * @property int $usable_ips
 * @property string $network_ip
 * @property string $broadcast_ip
 * @property int $ip_count
 * @property int $device_id
 * @property int $is_full
 * @property string $created_date
 * @property string $updated_date
 * @property int $is_active
 */
class Ipv6PoolAssignment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_ipv6_pool_assignment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pool_id', 'service_instance_id', 'subnet', 'usable_ips', 'network_ip', 'broadcast_ip', 'ip_count', 'device_id', 'created_date', 'updated_date', 'is_active'], 'required'],
            [['pool_id', 'service_instance_id', 'usable_ips', 'ip_count', 'device_id'], 'integer'],
            [['created_date', 'updated_date'], 'safe'],
            [['subnet'], 'string', 'max' => 3],
            [['network_ip', 'broadcast_ip'], 'string', 'max' => 40],
            [['is_full', 'is_active'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pool_id' => 'Pool ID',
            'service_instance_id' => 'Service Instance ID',
            'subnet' => 'Subnet',
            'usable_ips' => 'Usable Ips',
            'network_ip' => 'Network Ip',
            'broadcast_ip' => 'Broadcast Ip',
            'ip_count' => 'Ip Count',
            'device_id' => 'Device ID',
            'is_full' => 'Is Full',
            'created_date' => 'Created Date',
            'updated_date' => 'Updated Date',
            'is_active' => 'Is Active',
        ];
    }
    
    public static function getIpv6PoolAssignment($service_instance_id, $service_template_id, $device_id, $required_number_of_ip, $variable_arr = array()) {
        //pe($variable_arr);
	if(!empty($variable_arr))
        {
            $keyarr = array_keys($variable_arr);
            $query = new Query;
            $query->select(['tbl_variables_values.value1'])
                  ->from('tbl_variables_mapping')
                  ->join('INNER JOIN', 'tbl_variables_values',
                        'tbl_variables_values.variable_mapping_id = tbl_variables_mapping.id AND tbl_variables_mapping.variable_id = ' . $keyarr[0]); 
            $data = $query->createCommand()->queryOne();
            $ipv6Pool = $data['value1'];
            $ipv6PoolModel = Ipv6Pool::isPoolFull($ipv6Pool);
            /*if(count($variable_arr) > 0) {
                unset($variable_arr[$keyarr[0]]);
            }*/
            if($ipv6PoolModel != NULL && !empty($ipv6PoolModel))
            {
                $ipv6AddressArr = array();
                $isPoolCreated  = Ipv6PoolAssignment::find()->where(['pool_id' => $ipv6Pool, "service_instance_id" => $service_instance_id, "is_full" => 0, "is_active" => 1])->orderBy(['id' => SORT_DESC])->limit(1)->one();
                
                if($required_number_of_ip == 2 && $variable_arr[$keyarr[0]] == "ipv6-wan-address")
                {
                    $lastRecord     = Ipv6PoolAssignment::find()->where(['pool_id' => $ipv6Pool, "is_active" => 1])->orderBy(['id' => SORT_DESC])->limit(1)->one();
                    $startIp        = ($lastRecord)?IpAddressHelper::incrementIpAddress($lastRecord->broadcast_ip, 1):$ipv6PoolModel->pool;
                    $getSubnetting  = Ipv6Subnetting::find()->where(['>=', 'total_ips', $required_number_of_ip])->one();
                    $subnet         = $getSubnetting['subnet'];
                    $ipv6SubPoolObj = self::createIpv6SubPoolForWan($service_instance_id, $device_id, $ipv6Pool, $subnet, $getSubnetting['total_ips'], $startIp);
                    $ipv6AddressArr['subnet'] = $subnet;
                    $ipv6AddressArr[$variable_arr[$keyarr[0]]] = $ipv6SubPoolObj->network_ip;
                    $ipv6AddressArr[$variable_arr[$keyarr[1]]] = $ipv6SubPoolObj->broadcast_ip;
                    $ipv6SubPoolObj->is_full = 1;
                    $ipv6SubPoolObj->save(false);
                } else {
                    if(empty($isPoolCreated)) {
                        $lastRecord     = Ipv6PoolAssignment::find()->where(['pool_id' => $ipv6Pool, "is_active" => 1])->orderBy(['id' => SORT_DESC])->limit(1)->one();
                        $startIp        = ($lastRecord)?IpAddressHelper::incrementIpAddress($lastRecord->broadcast_ip, 1):$ipv6PoolModel->pool;
                        $getSubnetting  = Ipv6Subnetting::find()->where(['>=', 'useable_ips', $required_number_of_ip])->one();
                        $subnet         = $getSubnetting['subnet'];
                        $ipv6SubPoolId = self::createIpv6SubPool($service_instance_id, $device_id, $ipv6Pool, $subnet, $getSubnetting['useable_ips'], $startIp);
                    } else {
                        $ipv6SubPoolId  = $isPoolCreated['id'];
                        $subnet         = $isPoolCreated['subnet'];
                    }
                    $ipv6AddressArr['subnet'] = $subnet;
                    foreach($variable_arr as $key => $ipfield)
                    {
                        $getFirstDeviceIp  = DeviceVariablesMapping::find()->where(['service_instance_id' => $service_instance_id, 'service_model_template_id' => $service_template_id, "resource_manager_id" => $key])->orderBy(['id' => SORT_DESC])->one();
                        if($getFirstDeviceIp)
                        {
                            if($device_id != $getFirstDeviceIp['device_id']) {
                                if(stristr($ipfield, 'wan') !== FALSE) {
                                    $fetchFromPool = $this->fetchIpv6Address($ipv6SubPoolId);
                                    $fetchFromInventory = IpAddressHelper::incrementIpAddress($getFirstDeviceIp['value'], 1);
                                    if($fetchFromPool == $fetchFromInventory) {     
                                        $ipv6AddressArr[$ipfield] = $fetchFromInventory;
                                    }
                                } else {
                                    $ipv6AddressArr[$ipfield] = $getFirstDeviceIp['value'];
                                }
                            }
                        } else {
                            $ipv6AddressArr[$ipfield] = self::fetchIpv6Address($ipv6SubPoolId);
                        }
                    }
                }
		//pe($ipv6AddressArr);
                return $ipv6AddressArr;
            }
        }
    }
    
    public static function createIpv6SubPool($service_instance_id, $device_id, $ipv6Pool, $subnet, $usable_ip, $startIp) {
        $ipv6Assignment = new Ipv6PoolAssignment();
        $ipv6Assignment->pool_id = $ipv6Pool;
        $ipv6Assignment->service_instance_id = $service_instance_id;
        $ipv6Assignment->subnet = $subnet;
        $ipv6Assignment->usable_ips = $usable_ip;
        $ipv6Assignment->network_ip = $startIp;
        $ipv6Assignment->broadcast_ip = IpAddressHelper::incrementIpAddress($startIp, ($usable_ip + 1));
        $ipv6Assignment->device_id = $device_id;
        $ipv6Assignment->created_date = '2018-05-10 08:46:00';
        $ipv6Assignment->updated_date = '2018-05-10 08:46:00';
        if($ipv6Assignment->save(false)) {
            return $ipv6Assignment->id;
        } else {
            return null;
        }
    }
    
    public static function createIpv6SubPoolForWan($service_instance_id, $device_id, $ipv6Pool, $subnet, $usable_ip, $startIp) {
        $ipv6Assignment = new Ipv6PoolAssignment();
        $ipv6Assignment->pool_id = $ipv6Pool;
        $ipv6Assignment->service_instance_id = $service_instance_id;
        $ipv6Assignment->subnet = $subnet;
        $ipv6Assignment->usable_ips = $usable_ip;
        $ipv6Assignment->network_ip = $startIp;
        $ipv6Assignment->broadcast_ip = IpAddressHelper::incrementIpAddress($startIp, 1);
        $ipv6Assignment->device_id = $device_id;
        $ipv6Assignment->created_date = '2018-05-10 08:46:00';
        $ipv6Assignment->updated_date = '2018-05-10 08:46:00';
        if($ipv6Assignment->save(false)) {
            return $ipv6Assignment;
        } else {
            return null;
        }
    }
    
    public static function fetchIpv6Address($ipv6SubPoolId) {
        if (($model = Ipv6PoolAssignment::findOne($ipv6SubPoolId)) !== null) {
            $nextIp = IpAddressHelper::incrementIpAddress($model->network_ip, $model->ip_count);
            if($model->ip_count == $model->usable_ips)
            {
                $model->is_full = 1;
            } else {
                $model->ip_count = $model->ip_count + 1;
            }
            $model->save(false);
            return $nextIp;
        }
    }
    
    /*Relation with device*/
    public function getDevice() {
        return $this->hasOne(Device::className(), ["id" => "device_id"]);
    }

    /*Relation with pool*/
    public function getIpv6Pool() {
        return $this->hasOne(Ipv6Pool::className(), ["id" => "pool_id"]);
    }
    
    /*Relation with Service Instance*/
    public function getServiceInstance() {
        return $this->hasOne(ServiceInstance::className(), ["id" => "service_instance_id"]);
    }
}
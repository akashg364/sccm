<?php

namespace common\components;

use backend\models\DeviceVariablesMapping;
use backend\models\Ipv4Pool;
use backend\models\Ipv4Subnetting;
use common\components\IpAddressHelper;
use common\models\Ipv4PoolAssignment;
use common\models\Ipv4SubPool;
use yii\db\Query;
use Yii;

Class Ipv4Assignment extends \yii\base\Component {

    public $errors;

    public function getIpv4Pool($ipv4_pool_id) {
        return Ipv4Pool::find()->alias("ip")
            ->where(["ip.id" => $ipv4_pool_id])
            ->one();
    }

    public function getIpv4Subnetting($required_ips) {
        $getSubnetting = Ipv4Subnetting::find()
            ->where(['>=', 'usable_ips', $required_ips])
            ->andWhere(["NOT IN","id",[31,32]])
            ->orderBy(["usable_ips" => SORT_ASC])
            ->limit(1)
            ->one();
        return $getSubnetting;
    }
    
    public function getIpv4SubnettingBySubnet($subnet) {
        $getSubnetting = Ipv4Subnetting::find()
            ->where(['subnet'=> $subnet])
            ->orderBy(["usable_ips" => SORT_ASC])
            ->limit(1)
            ->one();
        return $getSubnetting;
    }
    
    public function assingIpBySubnet(){
            
    }
    
    public function asssignIp($service_instance_id,$service_template_id,$device_id, $no_of_ips, $variable_arr = [],$subnet=0) {
            
        //pr($service_instance_id,$service_template_id,$device_id, $no_of_ips, $variable_arr,$subnet);
            
        $keyarr = array_keys($variable_arr);

        $query = new Query;
        $query->select(['tbl_variables_values.value1'])
              ->from('tbl_variables_mapping')
              ->join('INNER JOIN', 'tbl_variables_values',
                    'tbl_variables_values.variable_mapping_id = tbl_variables_mapping.id AND tbl_variables_mapping.variable_id = ' . $keyarr[0]); 

        $data = $query->createCommand()->queryOne();
        $ipv4_pool_id = $data['value1'];
        $ipv4Pool = Ipv4Pool::isPoolFull($ipv4_pool_id);
        if (!$ipv4Pool) {
            $this->errors = "Given Ipv4 Pool ($ipv4_pool_id) is full";
            return false;
        }

        $assignedPool = $this->asssignPool($ipv4_pool_id, $device_id, $no_of_ips,$subnet);
        if (!$assignedPool) {
            return false;
        }
        if ($assignedPool && $assignedPool->is_full) {
            $this->errors = "Given Ipv4 Pool {$ipv4_pool_id} is full for no of ips {$no_of_ips}";
            return false;
        }

        $ipv4AddressArr = [];
        if(!empty($subnet)){	// If subnet comming as UD 
        	$i=1;
    	 	$model = Ipv4PoolAssignment::findOne($assignedPool->id);        	
	    	 foreach ($variable_arr as $key => $ipfield) {
	    	 	if($i==1 && $subnet==31){
	    	 		$ipv4AddressArr[$ipfield] = $this->getNextIpv4Address($assignedPool,true);
		 		}else{
		 			$ipv4AddressArr[$ipfield] = $this->getNextIpv4Address($assignedPool);
		 		}
		 		$i++;
	    	 }
        }else{
        	 foreach ($variable_arr as $key => $ipfield) {

	            $getFirstDeviceIp = DeviceVariablesMapping::find()
	                ->where([
	                    'service_instance_id' => $service_instance_id,
	                    'service_model_template_id' => $service_template_id,
	                    "resource_manager_id" => $key,
	                ])
	                ->orderBy(['id' => SORT_DESC])->one();

	            if ($getFirstDeviceIp && $device_id != $getFirstDeviceIp['device_id']) {
	                if (stristr($ipfield, 'wan') !== FALSE) {
	                    $fetchFromPool = $this->getNextIpv4Address($assignedPool);
	                    $fetchFromInventory = IpAddressHelper::incrementIpAddress($getFirstDeviceIp['value'], 1);
	                    if ($fetchFromPool == $fetchFromInventory) {
	                        $ipv4AddressArr[$ipfield] = $fetchFromInventory;
	                    }
	                } else {
	                    $ipv4AddressArr[$ipfield] = $getFirstDeviceIp['value'];
	                }
	            } else {
	                $ipv4AddressArr[$ipfield] = $this->getNextIpv4Address($assignedPool);
	            }
	        }

	        if($no_of_ips == 1 && $ipfield == "ipv4-wan-address"){
	                $ip = $ipv4AddressArr[$ipfield];
	                $ipArr = explode(".", $ip);
	                $lastOctate = end($ipArr);
	                if($lastOctate%2!=0){
	                    return $this->asssignIp($service_instance_id,$service_template_id,$device_id, $no_of_ips, $variable_arr);
	                }
	        }

        }
       

        return $ipv4AddressArr;

    }

    public function getNextIpv4Address($assignedPool,$skipIpIncrement=false) {
        if ($model = Ipv4PoolAssignment::findOne($assignedPool->id)) {
        	
            $nextIp = $skipIpIncrement?$model->start_ip:IpAddressHelper::incrementIpAddress($model->start_ip, $model->ip_count);

            if($skipIpIncrement){
            	$model->ip_count = 1;
            }else {
                $model->ip_count = $model->ip_count + 1;
            }
            $model->save(false);
           	if ($model->ip_count == $model->usable_ips) {
                $model->is_full = 1;
                 $model->save(false);
            }
            return $nextIp;
        } else {
            $this->errors = $model->errors;
            return false;
        }
    }

    public static function checkPoolAssigned($device_id, $subnet) {
        $model = Ipv4PoolAssignment::find()
            ->where(['device_id' => $device_id, 'subnet' => $subnet])
            ->one();
        return $model;
    }

    public function asssignPool($ipv4_pool_id, $device_id, $requiredHosts,$subnet=0) {    	
        $ret = [];
        $transaction = Yii::$app->db->beginTransaction();

        try {

            $ipv4Pool = $this->getIpv4Pool($ipv4_pool_id);
            $poolArr = explode("/", $ipv4Pool->pool);
            
            if(!empty($subnet)){
                $ipv4Subnetting = $this->getIpv4SubnettingBySubnet($subnet);
                if($subnet==31){
                 	$usable_ips = $ipv4Subnetting->total_ips;
                }else{
                 $usable_ips = $ipv4Subnetting->usable_ips;                	
                }

            }else{
                $ipv4Subnetting = $this->getIpv4Subnetting($requiredHosts);
                 $usable_ips = $ipv4Subnetting->usable_ips;
            }
            if (!$ipv4Subnetting) {
                $this->errors = "Ipv4 Subnetting not found for no of ips " . $requiredHosts;
                return false;
            }
            $subnet = $ipv4Subnetting->subnet;

            $poolAssigned = self::checkPoolAssigned($device_id, $subnet);
            if (!empty($poolAssigned)) {
                $transaction->commit();
                return $poolAssigned;
            } else {
                $ipv4SubPool = self::getVacantIpPool($ipv4_pool_id, $requiredHosts);
                if (!empty($ipv4SubPool)) {

                    $new_ip_pool = $ipv4SubPool->ip_pool;
                    $arr_pool = self::getStartingIpFromIpPool($ipv4SubPool->id, $requiredHosts);
                    if (!empty($arr_pool)) {
                        $new_ip_pool = $arr_pool;
                    }
                    $arrIPv4s = IpAddressHelper::generateIPv4Pool($new_ip_pool, $subnet, true);

                    // Add in assignement pool
                    $ipv4PoolAssignmentModel = new Ipv4PoolAssignment();
                    $ipAssignment['sub_ip_pool_id'] = $ipv4SubPool->id;
                    $ipAssignment['subnet'] = $subnet;
                    $ipAssignment['start_ip'] = $arrIPv4s[0];
                    $ipAssignment['end_ip'] = end($arrIPv4s);
                    $ipAssignment['device_id'] = $device_id;
                    $ipAssignment['usable_ips'] = $usable_ips;
                    //$ipAssignment['ip_count'] = 1; //$requiredHosts;
                    $ipv4PoolAssignmentModel->attributes = $ipAssignment;
                    if (!$ipv4PoolAssignmentModel->save()) {
                        $this->errors = $ipv4PoolAssignmentModel->errors;
                        return false;
                    }

                    // Update in sub pool
                    $ipv4SubPool->ip_count = $ipv4SubPool->ip_count + count($arrIPv4s);
                    if (!$ipv4SubPool->save()) {
                        $this->errors = $ipv4SubPool->errors;
                        return false;
                    }

                    $transaction->commit();
                    return $ipv4PoolAssignmentModel;
                }
            }

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public static function getVacantIpPool($ipv4_pool_id, $requiredHosts) {
        $ipv4SubPool = Ipv4SubPool::findAll(['ipv4_pool_id' => $ipv4_pool_id]);
        if (!empty($ipv4SubPool)) {
            foreach ($ipv4SubPool as $key => $val) {
                if ((256 - $val->ip_count) >= $requiredHosts) {
                    return $val;
                }
            }
        }
        return self::getNewIpPool($ipv4_pool_id);
    }

    public static function getNewIpPool($ipv4_pool_id) {

        $ipv4SubPool = Ipv4SubPool::findAll(['ipv4_pool_id' => $ipv4_pool_id]);
        $new_ip_pool = '';
        if (!empty($ipv4SubPool)) {

            foreach ($ipv4SubPool as $key => $val) {
                $ip_pool_list[] = $val->ip_pool;
            }
            asort($ip_pool_list, SORT_NATURAL);

            $last_ip_pool = end($ip_pool_list);
            $new_ip_pool = IpAddressHelper::incrementIpAddress($last_ip_pool, 256);

        } else {

            //if not existing then the new one pool from master
            $ipv4Pool = Ipv4Pool::findOne(['id' => $ipv4_pool_id]);
            $pool = $ipv4Pool->pool;
            $poolArr = explode("/", $pool);
            $last_ip_pool = $poolArr[0];
            $new_ip_pool = IpAddressHelper::incrementIpAddress($last_ip_pool, 0);
        }

        $model = new Ipv4SubPool();
        $model->ipv4_pool_id = $ipv4_pool_id;
        $model->ip_pool = $new_ip_pool;
        $model->ip_count = 0;
        $model->created_date = date("Y-m-d H:i:s");
        $model->modified_date = date("Y-m-d H:i:s");

        return $model->save() ? $model : false;
    }

    public static function getStartingIpFromIpPool($sub_ip_pool_id, $requiredHosts) {

        $ip_details = Ipv4PoolAssignment::find()
            ->where(['sub_ip_pool_id' => $sub_ip_pool_id])
            ->asArray()
            ->all();

        if (!empty($ip_details)) {
            $ip = array_column($ip_details, 'end_ip');
            asort($ip, SORT_NATURAL);
            $last_ip_pool = end($ip);

            //START : generate IP pool according to required hosts -- Kinjal
            list($first, $second, $third, $lastOctate) = explode(".", $last_ip_pool);

            $gapFinder = ($requiredHosts - (($lastOctate + 1) % $requiredHosts));
            if ($gapFinder == $requiredHosts) {
                $last_ip_pool = $last_ip_pool;
            } else {
                $last_ip_pool = ($first . "." . $second . "." . $third . "." . ($lastOctate + $gapFinder));
            }
            //END : generate IP pool according to required hosts -- Kinjal

            $new_ip_pool = IpAddressHelper::incrementIpAddress($last_ip_pool, 1);
            return $new_ip_pool;
        }
        return false;
    }

    public function getHostname() {
        $digits = 4;
        $lastdigit = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
        $hostname = 'HLHFRBFRCES9' . $lastdigit;
        return $hostname;
    }

}
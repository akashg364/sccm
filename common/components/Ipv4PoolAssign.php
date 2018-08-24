<?php

namespace common\components;

use common\components\IpAddressHelper;
use Yii;

Class Ipv4PoolAssign extends \yii\base\Component {

	public static function getVacantIpPool($requiredHosts, $vlan, $pool_type = NULL, $service_type = NULL) {
		if ($pool_type == 'ACI') {
			$vrf_type = AciVlanMaster::getVrf($vlan);
			if ((strtoupper($vrf_type) == 'RJIL-CORE-MGMT' || strtoupper($vrf_type) == 'RJIL-IP-MGMT' || strtoupper($vrf_type) == 'RJIL-OSS-NOC-MGMT') && $service_type == 'IMS' && intval($vlan) != 163) {
				$service_type = 'CORE-MGMT';
			}
		} else {
			$vrf_type = NexusServicesVlanMapping::getVrf($vlan);
		}

		$criteria = new CDbCriteria();
		$criteria->select = 't.* ';
		if ($pool_type != NULL && $service_type != NULL) {
			$criteria->condition = 't.pool_type = :pool_type AND t.service_type = :service_type';
			$criteria->params = array(':pool_type' => $pool_type, ':service_type' => $service_type);
			$criteria->compare('ip_count', 256, false, '>');
		}
		$subIpData = NexusSubIpPool::model()->findAll($criteria);
		if (!empty($subIpData)) {
			if ((strtoupper($vrf_type) == 'RJIL-CORE-MGMT' || strtoupper($vrf_type) == 'RJIL-IP-MGMT' || strtoupper($vrf_type) == 'RJIL-OSS-NOC-MGMT') && $pool_type != 'ACI') {
				foreach ($subIpData as $key => $val) {
					$pool_ipArr = explode('.', $val->ip_pool);
					if (trim($pool_ipArr[1]) == 3 && trim($pool_ipArr[2]) >= 0 && trim($pool_ipArr[2]) <= 255) {
						if ((256 - $val->ip_count) >= $requiredHosts) {
							return $val;
						}
					}
				}
			} else if ($vlan == 163 && $pool_type == 'ACI' && $service_type == 'EPC') {
				foreach ($subIpData as $key => $val) {
					$pool_ipArr = explode('.', $val->ip_pool);
					if (trim($pool_ipArr[0]) == 130 && trim($pool_ipArr[1]) == 26 && trim($pool_ipArr[2]) >= 8) {
						if ((256 - $val->ip_count) >= $requiredHosts) {
							return $val;
						}
					}
				}
			} else {
				foreach ($subIpData as $key => $val) {
					$pool_ipArr = explode('.', $val->ip_pool);
					if ($pool_type == 'ACI') {
						if ((strtoupper($vrf_type) == 'RJIL-CORE-MGMT' || strtoupper($vrf_type) == 'RJIL-IP-MGMT' || strtoupper($vrf_type) == 'RJIL-OSS-NOC-MGMT') && $service_type == 'CORE-MGMT') {
							if (trim($pool_ipArr[0]) == 56 && trim($pool_ipArr[1]) == 4 && trim($pool_ipArr[2]) >= 0 && trim($pool_ipArr[2]) <= 255) {
								if ((256 - $val->ip_count) >= $requiredHosts) {
									return $val;
								}
							}
						} else {
							if ((256 - $val->ip_count) >= $requiredHosts) {
								return $val;
							}
						}
					} else {
						if (trim($pool_ipArr[1]) == 0 && (trim($pool_ipArr[2]) >= 81 && trim($pool_ipArr[2]) <= 247) && (trim($pool_ipArr[3]) >= 0 && trim($pool_ipArr[3]) <= 255)) {
							if ((256 - $val->ip_count) >= $requiredHosts) {
								return $val;
							}
						}
					}
				}
			}
		}
		$first_row_model = NexusSubIpPool::getNewIpPool($vlan, 0, $pool_type, $service_type);
		if ($requiredHosts == 512) {
			$got_ip_pool = false;
			while (!$got_ip_pool) {
				$ip_pool_array = explode('.', $first_row_model->ip_pool);
				if (($ip_pool_array[2] % 2) == 0) {
					$got_ip_pool = true;
					break;
				} else {
					$first_row_model = NexusSubIpPool::getNewIpPool($vlan, 0, $pool_type, $service_type);
				}
			}
			NexusSubIpPool::getNewIpPool($vlan, 256, $pool_type, $service_type);
		}
		return $first_row_model;
	}

	public static function getNewIpPool($vlan, $ip_count = 0, $pool_type = null, $service_type = null) {
		if ($pool_type == 'ACI') {
			$vrf_type = AciVlanMaster::getVrf($vlan);
			if ((strtoupper($vrf_type) == 'RJIL-CORE-MGMT' || strtoupper($vrf_type) == 'RJIL-IP-MGMT' || strtoupper($vrf_type) == 'RJIL-OSS-NOC-MGMT') && $service_type == 'IMS' && intval($vlan) != 163) {
				$service_type = 'CORE-MGMT';
			}
		} else {
			$vrf_type = NexusServicesVlanMapping::getVrf($vlan);
		}
		$criteria = new CDbCriteria();
		$criteria->select = 't.* ';
		if ($pool_type != NULL && $service_type != NULL) {
			$criteria->condition = 't.pool_type = :pool_type AND t.service_type = :service_type';
			$criteria->params = array(':pool_type' => $pool_type, ':service_type' => $service_type);
		}

		$subIpData = NexusSubIpPool::model()->findAll($criteria);
		$new_ip_pool = array();
		if (!empty($subIpData)) {
			foreach ($subIpData as $key => $val) {
				$pool_ipArr = explode('.', $val->ip_pool);
				if ((strtoupper($vrf_type) == 'RJIL-CORE-MGMT' || strtoupper($vrf_type) == 'RJIL-IP-MGMT' || strtoupper($vrf_type) == 'RJIL-OSS-NOC-MGMT') && $pool_type != 'ACI') {
					if (trim($pool_ipArr[1]) == 3 && trim($pool_ipArr[2]) >= 0 && trim($pool_ipArr[2]) <= 255) {
						$ip_pool_list[] = $val->ip_pool;
					}
				} else {
					if (trim($pool_ipArr[1]) == 0 && (trim($pool_ipArr[2]) >= 81 && trim($pool_ipArr[2]) <= 247) && (trim($pool_ipArr[3]) >= 0 && trim($pool_ipArr[3]) <= 255)) {
						$ip_pool_list[] = $val->ip_pool;
					}
				}
				if ($pool_type == 'ACI') {
					if ($vlan == 163) {
						if (trim($pool_ipArr[0]) == 130 && trim($pool_ipArr[1]) == 26 && trim($pool_ipArr[2]) >= 8) {
							$ip_pool_list[] = $val->ip_pool;
							$master_pool_id = $val->master_id;
						}
					} elseif ((strtoupper($vrf_type) == 'RJIL-CORE-MGMT' || strtoupper($vrf_type) == 'RJIL-IP-MGMT' || strtoupper($vrf_type) == 'RJIL-OSS-NOC-MGMT') && $service_type == 'CORE-MGMT') {
						$service_type = 'CORE-MGMT';
						if (trim($pool_ipArr[0]) == 56 && trim($pool_ipArr[1]) == 4) {
							$ip_pool_list[] = $val->ip_pool;
							$master_pool_id = $val->master_id;
						}
					} else {
						$ip_pool_list[] = $val->ip_pool;
						$master_pool_id = $val->master_id;
					}
				}
			}
			$new_ip_pool = '';
			if (!empty($ip_pool_list)) {
				asort($ip_pool_list, SORT_NATURAL);
				$last_ip_pool = end($ip_pool_list);
				$new_ip_pool = IpAddressHelper::incrementIpAddress($last_ip_pool, 256);
			}
		}
		//if not existing then the new one pool from master

		if (empty($new_ip_pool)) {
			$objMasterPool = NexusMasterIpPool::getMasterPool($pool_type, $service_type);
			$last_ip_pool = $objMasterPool->ipv4_pool;

			if ($vlan == 3075) {
				$new_ip_pool = '56.0.248.0';
			} else if ((strtoupper($vrf_type) == 'RJIL-CORE-MGMT' || strtoupper($vrf_type) == 'RJIL-IP-MGMT' || strtoupper($vrf_type) == 'RJIL-OSS-NOC-MGMT') && $pool_type != 'ACI') {
				$new_ip_pool = '56.3.0.0';
			} else {
				$new_ip_pool = '56.0.81.0';
				//$new_ip_pool = IpAddressHelper::incrementIpAddress($last_ip_pool, 0);
			}
			if ($pool_type == 'ACI' && $service_type) {
				$new_ip_pool = $last_ip_pool;
				$master_pool_id = $objMasterPool->id;
			}
		}

		$model = new NexusSubIpPool;
		$model->master_id = 1;
		$model->ip_pool = $new_ip_pool;
		$model->ip_count = $ip_count;
		if ($pool_type && $service_type) {
			$model->master_id = $master_pool_id;
			$model->pool_type = $pool_type;
			$model->service_type = $service_type;
		}
		$model->created_at = new CDbExpression('now()');
		if ($model->save(FALSE)) {
			return $model;
		}

	}

	public static function getStartingIpFromIpPool($sub_ip_pool_id, $requiredHosts) {

		$criteria = new CDbCriteria();
		$criteria->condition = 't.sub_ip_pool_id =:sub_ip_pool_id';
		$criteria->params = array(':sub_ip_pool_id' => $sub_ip_pool_id);
		$criteria->select = 't.end_ip ';
		$ip_details = NexusSviIpv4Pool::model()->findAll($criteria);
		if (!empty($ip_details)) {
			$ip = array();
			foreach ($ip_details as $key => $val) {
				$ip[] = $val->end_ip;
			}
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
	}

	public static function checkPreviouslyPoolAssigned($hostname, $vlanId, $subnet) {
		$criteria = new CDbCriteria();
		$criteria->condition = 't.hostname = :hostname AND vlan_id = :vlanId AND subnet = :subnet';
		$criteria->params = array(':hostname' => $hostname, ':vlanId' => $vlanId, ':subnet' => $subnet);
		$criteria->select = 't.* ';
		$ip_details = NexusSviIpv4Pool::model()->find($criteria);
		if (!empty($ip_details)) {
			return $ip_details;
		}
	}
}

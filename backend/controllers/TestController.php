<?php

namespace backend\controllers;

use backend\models\DeviceRole;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * DeviceRoleController implements the CRUD actions for DeviceRole model.
 */
class TestController extends Controller {
	/**
	 * @inheritdoc
	 */
	public function behaviors() {
		return [
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'delete' => ['POST'],
				],
			],
		];
	}

	/**
	 * Deletes an existing DeviceRole model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param string $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionTestEmail() {

		print "Testing";
		$from = "support@cnaap.net";
		$from_name = "CCM Team";
		$subject = "Test";

		$body_msg = "Testing";

		//$destination_path = array("/var/www/html/swim_dev/uploads/os_upgrade_report_$today.csv");
		$destination_path = '';

		$to = [
			["email" => "jitendra.gamit@infinitylabs.in", "name" => "Jitendra Gamit"],

		];

		$cc = [
			["email" => "jitendra123.gamit@infinitylabs.in", "name" => "Jitendra Gamit"],
		];

		$result = \common\components\CommonUtility::sendmailWithAttachment($to, $to_name = "", $from, $from_name, $subject, $body_msg,
			$destination_path, $cc);

		if ($result) {
			echo "Email Sent\n";
		} else {
			echo "Failed\n";
		}

	}


	/*
		Api test :
		Sample data : "hostname": "ABDDCMPYECR001",
		"category": "used"
	*/
	public function actionTestApi() {
		//$input = array("hostname"=>'ABDDCMPYECR001', "commandcode"=>'l2ringdata');
		//$ret = Yii::$app->inventoryApi->getCommandData($input);

		//$input = array("neid"=>"INMPAMWDXXXXTW0002ENBESR001");
		//$ret = Yii::$app->inventoryApi->getDeviceDetails($input);

		//$input = array("hostname" => 'AMWDAMWDESR009', "interface" => 'GigabitEthernet0/0/5', "commandcode" => 'encapsulation');
		//$ret = Yii::$app->inventoryApi->getEncapsulationVlan($input);

		$input = array("hostname" => 'AMWDAMWDESR009', "interface" => 'GigabitEthernet0/0/5', "commandcode" => 'encapsulation');
		$ret = Yii::$app->inventoryApi->encapsulaton_vlans($input);
		
		/*
			$input = array("pincode" => '400701',
						"device_type" => 'AG1');
			$ret = Yii::$app->inventoryApi->getNearByDevicesUsingPincode($input);
		*/

		pe($ret);
		exit;
	} // EO actionTestApi()

	public function actionVariableLogic() {
		$res = \backend\models\ResourceManagerLogic::enc_vlan('asdfasdf', 'fd', 'f');
		pe($res);
	}

	public function actionIpv4() {

		//$transaction = Yii::$app->db->beginTransaction();
		//try {	
			$obj = new \common\components\Ipv4Assignment();
			$res = $obj->asssignIp(
				$service_instance_id=8, 
				$service_template_id=1,
					$device_id=2, 	
					$required_number_of_ip=2, 
				//$ipv4_pool_id=1,						
				$variable_arr = [	
							26=>"ipv4-wan-address",
							27=>"ipv4-wan-address-1",
							//28=>"ipv4-wan-address-2",
							//29=>"ipv4-wan-address-3"
							],//["vip_pri","vip_sec","ipv4_wan_address"]
				$subnet = 30
			);

			pe($res,$obj->errors);
			// $transaction->commit();
		//}catch (\Exception $e) {
		    //$transaction->rollBack();
		   // throw $e;
		//}

	}
	
	public function actionPayload(){
		
		$ud = '{
    "demarc-egress-bridge-port-no-1": 8,
    "demarc-egress-bridge-port-no-2": 6,
    "ipv6-wan-address": "2405:0200:1410:1401:0000:0000:0004:0116/127,2405:0200:1410:1401:0000:0000:0004:0114/127",
    "demarc-erps-id-1": 1,
    "demarc-erps-id-2": 2,
    "remote-as": 64512,
    "demarc-erps-mode-2": "Sub",
    "demarc-erps-mode-1": "Sub",
    "demarc-ingress-port-no": "3",
    "pe-type": "dual",
    "demarc-device": "MUMBRLB1EDS002",
    "demarc-interface-id-2": "gig0/0/21",
    "tag": "tagged",
    "demarc-interface-id-1": "gig0/0/21",
    "demarc-raps-vlan-1": 405,
    "demarc-raps-vlan-2": 415,
    "lan-pool-ipv4-mask": "10.64.88.8/29",
    "lan-pool-ipv6-mask": "2405:0200:1410:1401:0000:0000:0004:0108/125 ",
    "bit-value": 4,
    "cust-vlan": "1990,1991",
    "demarc-inst-no-1": 405,
    "block-port-no": "0/1",
    "ipv4-wan-address": "10.64.88.20 255.255.255.252,10.64.88.22 255.255.255.252",
    "nei-ce-ip-ipv4": "10.64.88.21,10.64.88.23",
    "demarc-trunk-no-1": 1,
    "nei-ce-ip-ipv6": "2405:0200:1410:1401:0000:0000:0004:0115,2405:0200:1410:1401:0000:0000:0004:0117"
}';
		$userdefined = json_decode($ud,true);

		$payload = new \common\components\PayLoad();
		$devices = [
			"MUMBRLB1ESR003",
			"MUMBRLB1ESR004",
			"demarc_hostname"
		];
		$devices = [
				[
					'device'=>10
				],
				[
					'device'=>12
				],
		];
		$res = $payload->createPayload(8,$devices,$userdefined);

		if(!$res){
			pe($payload->errors);
		}
		pe($res);
		exit;
	}


	

	public function actionQuery(){
		$rows =  \backend\models\ServiceInstance::find()
		->where(["status"=>'1','action'=>[1,2,3]])
		->andWhere("scheduled_date <= NOW()")->asArray()->all();
		pe($rows);
	}
}

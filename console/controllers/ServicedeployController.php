<?php

namespace console\controllers;

Class ServicedeployController extends \yii\console\Controller {

	public function actionCommitQueue($id=""){	
		
		echo "Cron run at ".date("Y-m-d H:i:s")."\n";
		$obj = new \backend\models\ServiceDeploy();
		$obj->commitQueue($id);
		print_r($obj->errors);
	}
	
	
	public function actionCurl(){
	
	$chinit = curl_init("http://10.64.102.53/testp.php");
 curl_setopt($chinit, CURLOPT_HEADER, 0); 
 curl_setopt($chinit, CURLOPT_POST, 1); 
 curl_setopt($chinit, CURLOPT_RETURNTRANSFER, 1); 
 $result = curl_exec($chinit); curl_close($chinit); 
 echo $result; 
	}
}
/*
$chinit = curl_init("http://10.64.102.53/testp.php");
 curl_setopt($chinit, CURLOPT_HEADER, 0); 
 curl_setopt($chinit, CURLOPT_POST, 1); 
 curl_setopt($chinit, CURLOPT_RETURNTRANSFER, 1); 
 $result = curl_exec($chinit); curl_close($chinit); 
 echo $result; 
*/

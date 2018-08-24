<?php

namespace console\controllers;

Class CompareController extends \yii\console\Controller {

    public function actionTest() {
        $connection = ssh2_connect('10.70.4.9', 22);
        if (ssh2_auth_password($connection, 'NACAdmin', 'N@(Adm1n')) {
            echo 'connect';
        }
        $model = new \backend\models\ServiceDeploy;
        $data = $model->getServiceInstatnceData();
        foreach($data as $key=>$val){
            print_r($val['service_order_id']);
        }
    }
}

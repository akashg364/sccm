<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\db\Query;


/**
 * @SWG\Definition(
 *   definition="Sccmcrd",
 *   type="object",
 *   required={"service_id", "is_managed"}, 
 *       @SWG\Property(property="service_id", type="integer"),
 *       @SWG\Property(property="is_managed", type="integer"),
 *       @SWG\Property(property="terminated_at", type="integer"), 
 *       @SWG\Property(property="routing_protocol", type="integer"),
 *       @SWG\Property(property="dual", type="integer"),
 *       @SWG\Property(property="witheds", type="integer"), 
 * )
 */
class Sccmcrd extends ActiveRecord {

    public static function tableName() {
        return '{{%tbl_sccm_crd}}';
//        return '{{%sub_services}}';
    }

    // public static function getDb(){
    // 	return \Yii::$app->db_cnaap;
    // }

    public function rules() {
        return [
            [['service_id', 'subflavour_id', 'service_subflavour', 'is_managed', 'device_roles', 'assigned_image', 'comments', 'approved_details', 'clms_id_details'], 'safe'],
        ];
    }

    public static function getSubService($service_id, $is_managed, $terminated_at, $routing_protocol, $dual, $witheds) {

        $terminated_at = Subservicefilters::getFilterParam('terminated_at', $terminated_at);
        $routing_protocol = Subservicefilters::getFilterParam('routing_protocol', $routing_protocol);
        $dual = Subservicefilters::getFilterParam('dual', $dual);
        $EDS = Subservicefilters::getFilterParam('eds', $witheds);

//		$qry = Sccmcrd::find()
//					 ->where([
//						"service_id"=>$service_id,
//						"is_managed"=> $is_managed
//					])
//					->andWhere(["LIKE","service_subflavour","$terminated_at."])
//					->andWhere(["LIKE","service_subflavour","$routing_protocol."]);
//	
//				 $model= $qry->one();

        $query = new Query;
        $query->SELECT('rm.variable_name,rm.type,dr.role_name,ss.name,ss.ref_id')
                ->from('tbl_service_model')
                ->join("INNER JOIN", "tbl_service_model_template as smt ON (tbl_service_model.id = smt.service_model_id)")
                ->join("INNER JOIN", "tbl_service_model_template_variables as smtv ON (smtv.template_id=smt.id)")
                ->join("INNER JOIN", "tbl_device_role as dr ON (dr.id=smt.device_role_id)")
                ->join("INNER JOIN", "tbl_variables_master as rm ON (rm.id = smtv.resource_manager_id)")
                ->join("INNER JOIN", "tbl_sub_services as ss ON (ss.id = tbl_service_model.sub_service_id)")
        ->where([
        "is_managed" => $is_managed
        ])
        ->andWhere(["LIKE", "ss.name", "$terminated_at"])
        ->andWhere(["LIKE", "ss.name", "$routing_protocol"])
        ->andWhere(["LIKE", "ss.name", "$dual"])
        ->andWhere(["LIKE", "ss.name", "$EDS"]);
        
//        echo $query->createCommand()->getRawSql();exit;
        $model = $query->distinct()->all();

        if (!$model) {
            return false;
        }

//        $deviceRoles = Json::decode($model->device_roles);
//        print_r($model);

        $variables = ["system" => [], "user" => []];
        foreach ($model as $variable) {
            
            // if(trim($role_name) == "PE-Customer Facing"){
            // 		$role_name = "CFPE";
            // }
            // else if(trim($role_name) == "Demarc"){
            // 		$role_name = "DMAC";
            // }

//            foreach ($deviceRoleVariables as $roleVariable) {

//                $variables[$roleVariable["inputtype"]][$role_name][] = $roleVariable["variableId"];
            
                $variables[$variable['type']][] = $variable['variable_name'];
//            }
        }
        $data["sub_service"] = [
            "sub_service_id" => $model[0]['ref_id'],
            "service_name" => $model[0]['name'],
            "system_define" =>  array_unique($variables["system"]),
            "user_define" => array_unique($variables["user"]),
        ];
        return $data;
    }

}

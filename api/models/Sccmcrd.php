<?php

namespace api\models;

use yii\db\ActiveRecord;
use yii\helpers\Json;

 
/**
 * @SWG\Definition(
 *   definition="Subservice",
 *   type="object",
 *   required={"service_id","is_managed","terminated_at","routing_protocol"},
 *       @SWG\Property(property="service_id", type="interger"),
 *       @SWG\Property(property="is_managed", type="interger"),
 *       
 * )
 */
class Sccmcrd extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%tbl_sccm_crd}}';
    }
	
	// public static function getDb(){
	// 	return \Yii::$app->db_cnaap;
	// }
	
	public function rules()
    {
        return [
            [['service_id', 'subflavour_id','service_subflavour','is_managed','device_roles','assigned_image','comments','approved_details','clms_id_details'], 'safe'],        
        ];
    }
	
	
	public static function getSubService($service_id,$is_managed,$terminated_at,$routing_protocol,$topology_type='') {
	

		$terminated_at = Subservicefilters::getFilterParam('terminated_at',$terminated_at);
		$routing_protocol  = Subservicefilters::getFilterParam('routing_protocol',$routing_protocol);
	
		$qry = Sccmcrd::find()
					 ->where([
						"service_id"=>$service_id,
						"is_managed"=> $is_managed
					])
					->andWhere(["LIKE","service_subflavour","$terminated_at."])
					->andWhere(["LIKE","service_subflavour","$routing_protocol."]);
	
				 $model= $qry->one();
					 
			if(!$model){
				return false;				
			}		 
			
			$deviceRoles = Json::decode($model->device_roles);
			
			$variables = ["SD"=>[],"UD"=>[]];
			foreach($deviceRoles as $role_name => $deviceRoleVariables){
				if(trim($role_name) == "PE-Customer Facing"){
						$role_name = "CFPE";
				}
				else if(trim($role_name) == "Demarc"){
						$role_name = "DMAC";
				}
				
				foreach($deviceRoleVariables as $roleVariable){
					
					$variables[$roleVariable["inputtype"]][] = $role_name."_".$roleVariable["variableId"];
				}
			}		
			
		
			$data["subservice"]	= [
				"sub_service_id"=>$model->subflavour_id,
				"service_name"=> $model->service_subflavour,
				"system_define"=>$variables["SD"],
				"user_define"=>$variables["UD"],
			];
	  return $data;
	}
  
}
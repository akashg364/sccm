<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\DeviceVariablesMapping;
use backend\models\ResourceManager;
use backend\models\Customer;
use backend\models\Services;
use backend\models\SubServices;
use backend\models\RtRange;
use backend\models\VariablesValues;
use backend\models\ServiceModelTemplateVariables;
use backend\models\Ipv6PoolAssignment;
use yii\db\Query;
use common\components\Ipv4Assignment;
/**
 * This is the model class for table "resource_manager".
 *
 * @property int $id
 * @property int $customer_id
 * @property string $type
 * @property int $data_type_id
 * @property string $value_type
 * @property string $variable_name
 * @property string $variable_value
 * @property tinyint $active_status
 * @property tinyint $approve_status
 * @property int $created_by
 * @property string $created_date
 * @property int $updated_by
 * @property string $updated_date
 */
class ResourceManager extends \common\models\BaseModel
{
    use ResourceManagerLogic;
    
    public $parameter_min_value;
    public $parameter_max_value;
    public static $variable_errors = [];

    public static $parameterTypes = [
        "user"  =>"User Defined Parameter",
        "system"=>'System Defined Parameter'
    ];

    public static $valueTypes = [
        "range" =>"Range",
        "string"=>"String",
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_resource_manager';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'type', 'data_type_id', 'value_type', 'variable_name'], 'required'],
            ['parameter_min_value','required','when'=>function($model){
                return $model->type=='system'?true:false;        
            },'whenClient' => "function (attribute, value) { return $('#resourcemanager-type input[type=radio]')=='system';}"],
            ['parameter_max_value','required','when'=>function($model){
                return $model->value_type=='range'?true:false;              
            },'whenClient' => "function (attribute, value) { return $('#resourcemanager-value_type').val()=='range';}"],
            [['customer_id', 'data_type_id', 'created_by', 'updated_by'], 'integer'],
            [['created_date', 'updated_date'], 'safe'],
            [['value_type'], 'string', 'max' => 20],
            [['variable_name'], 'string', 'max' => 30],
            [['variable_value'], 'string', 'max' => 100],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\BlameableBehavior::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer',
            'type' => 'Type',
            'data_type_id' => 'Data Type',
            'value_type' => 'Value Type',
            'variable_name' => 'Variable Name',
            'variable_value' => 'Variable Value',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
            'updated_by' => 'Updated By',
            'updated_date' => 'Updated Date',
            'active_status' =>  'Active Status',  
            'approve_status' => 'Approve Status',
            'parameter_min_value'=>"Variable Min Value",
            'parameter_max_value'=>"Variable Max Value",
        ];
    }

    public static function getVariables($type=''){
        $query = self::find()->where(["active_status"=>1]);
                if($type){
                    $query->andWhere(["type"=>$type]);
                }
        $data = $query->asArray()->all();
        return $data;
    }


    public static function getUserSystemVariablesList($type=''){
        $ret["user"] = [];
        $ret["system"] = []; 
        $query = self::find()->where(["active_status"=>1]);
                if($type){
                    $query->andWhere(["type"=>$type]);
                }
        $data = $query->asArray()->all();
       
        foreach ($data as $key => $d) {
            $ret[$d["type"]][$d["id"]] = $d["variable_name"];
        }
        return $ret;
    }
    
    public static function getSystemDefinedArray($role_id, $device, $service_model_id) {
        $systemDefinedValue = array();
        $systemDefinedKey = array();

        $serviceTemplate = ServiceModelTemplate::find()
                ->where(['service_model_id' => $service_model_id, 'device_role_id' => $role_id])
                ->One();
        $serviceVariables = ServiceModelTemplateVariables::find()
                ->where(['template_id' => $serviceTemplate['id']])
                ->All();

        if (count($serviceVariables) > 0) {
            foreach ($serviceVariables as $value) {
				
                $resourceVariables = VariablesMaster::find()
                ->where(['id' => $value->resource_manager_id, 'type' => 'system'])
                ->One();
				if(isset($resourceVariables['type']) && $resourceVariables['type'] == 'system')
                {
					if(!empty($value->resource_manager_id)) {	
						$systemDefinedValue[$resourceVariables['variable_name']] = self::getSystemDefinedRange($value->resource_manager_id);
						$systemDefinedKey[$resourceVariables['id']] = self::getSystemDefinedRange($value->resource_manager_id);
					}
                }
            }
        }
        return array("value" => $systemDefinedValue, "key" => $systemDefinedKey);
    }
    
    public static function getSystemDefinedRange($id) {
        $definedRange = VariablesValues::find()
        ->where(['id' => $id])
        ->One();
        
		
        return $definedRange['value1'];
    }
    
    public static function getSystemDefinedValue($service_model_template_id, $device_id, $resource_manager_id)
    {
        $resource   = ResourceManager::find()->where(['id'=>$resource_manager_id])->asArray()->one();
        if($resource['value_type'] == 'range'){
            $range  =   explode(",",$resource['variable_value']);
            $query   = DeviceVariablesMapping::find()->where(['and',['service_model_template_id' => $service_model_template_id, 'device_id'=> $device_id, 'resource_manager_id'=>$resource_manager_id]]);
            $data   =   $query->asArray()->one();            
            if(!empty($data)){                
                if($data['value'] >= $range[0] && $data['value'] < $range[1]){
                    return $data['value']+1;
                }else{
                    return false;
                }
            }else{
                return $range[0];
            }
        }        
    }
      
    /*
     * Interface Description Format
     * Below is the format that would be used for adding description on the interface where the customer link would be terminated. 
     * This Parameters would be used in all the physical & logical Interface created for that customer (e.g. BDI interface). 
     * 
     * Format - <Type Of interface>:< Type of Link>:<Type of Service>:<Type of Product>:<CustomerName>:<BW/Speed>:<Ckt-ID>
     * ------------------------------------------------------------------------------------------------------------------
     * Type Of interface : UNI - User Network Interface, This would define that this link is used for Customer connection
     * Type of Link: ENT - This would help us to understand that this link is used for providing services for Enterprise customers.
     * Type of Service
     * ----------------------------
     * Service-Code  Service Type
     * ----------------------------
     * MV            Managed VPN Customers (All Services Excluding ILL)
     * NV            Non Managed VPN Customers (All Services Excluding ILL)
     * MI            Managed Internet Customers (ILL)
     * NI            Non Managed Internet Customers (ILL)
     * 
     * Type of Product  Note: If required additional Service type can be created basis product differentiation
     * --------------------------
     * Service-Code  Service Type
     * --------------------------
     * VPN           L3-VPN Service / SIP Trunk / IP centrix
     * VPS           VPLS Service (identyfy from subservice name) = VPS
     * VLL           P2P L2-VPN Service (get it from name of the subservice ) = VLL
     * IES           Internet Services (ILL)
     *
     * Customer-Name: Meaning full Customer Name (8 digit) should be min-1 and max-8 digit
     * BW/Speed     : Bandwidth required/configured for that Last mile.
     * Ckt-ID/Service order Number: This should define individual circuit ID specific to each site.
     * 
     * 
     * Example : ResourceManager::getInterfaceDescription(1, 23, "managed");
     */
    public static function getInterfaceDescription($serviceInstance, $bandwidth, $isManaged) { //demarc_phy_intf
        $interfaceType = "UNI";
        $linkType      = "ENT";
        
        $serviceInstance  = ServiceInstance::find()->where(['id' => $serviceInstance])->One();
        $serviceModel     = ServiceModel::find()->where(['id' => $serviceInstance['service_model_id']])->One();
        
        $service_order_no = $serviceInstance['service_order_id'];
        $serviceName      = Services::getServiceName($serviceModel['service_id']);
        $subService       = SubServices::getSubServiceName($serviceModel['sub_service_id']);
        $customerName     = Customer::getCustomerName($serviceInstance['customer_id']);
        $customerName     = str_replace(" ", "", $customerName);
        $customerName     = substr($customerName, 0, 8);

        if ($isManaged == "managed") {
            $serviveType   = ($serviceName == "ILL")?"MI":"MV";
        } else if($isManaged == "unmanaged") {
            $serviveType   = ($serviceName == "ILL")?"NI":"NV";
        }
        $productType    =   "";
        if ($serviceName == "L3VPN" || $serviceName == "SIP" || $serviceName == "IP-CENTRIX" || $serviceName == "l2vpn") {
            $productType   = "VPN";
        } else if($serviceName == "ILL") {
            $productType   = "IES";
        } else if(strpos($subService, 'VPS') !== false) {
            $productType   = "VPS";
        } else if(strpos($subService, 'VLL') !== false) {
            $productType   = "VLL";
        }
        
        $interfaceDescription = $interfaceType . ":" . $linkType . ":" . $serviveType . ":" . $productType . ":" . $customerName . ":" . $bandwidth . ":" . $service_order_no;
        return $interfaceDescription;
    }

    /*
     * VLAN range reserved for Enterprise Services.
     * VLAN Range- Product Specific
     * -----------------------------------------
     * VLAN                             Range
     * -----------------------------------------
     * VPN – Ethernet LM (L3VPN,L2VPN)  2101-2250 
     * ILL                              2311-2400 
     * SIP Trunk / IP Centrix           1612 to 1624 and 1627 to 1651 
     */
    public static function getCustomerServiceVlan($service_model_template_id, $device_id, $resource_manager_id, $customer_id){               
        $variablesMapping   = VariablesMapping::find()->where(['variable_id'=>$resource_manager_id,'customer_id'=>$customer_id])->one();
        $variableValues     = VariablesValues::find()->where(['variable_mapping_id'=>$variablesMapping['id'],'is_completed'=>0])->one();
        $query   = DeviceVariablesMapping::find()->where(['and',['service_model_template_id' => $service_model_template_id, 'device_id'=> $device_id, 'resource_manager_id'=>$resource_manager_id]])->orderBy(['id'=>SORT_DESC]);
        $data   =   $query->asArray()->one(); 
        $value  =   (int)$data['value'];
        $min    =   (int)$variableValues['value1'];
        $max    =   (int)$variableValues['value2'];
        if(!empty($data)){                
                if($min >= $variableValues['value1'] && $value < $max){                    
                    $customerServiceVlan    =    $value+1;
                    if($customerServiceVlan   ==  $max){
                        $modelVariableValues   = new VariablesValues();
                        $modelVariableValues->is_completed  =   1;
                        $modelVariableValues->id    =   $variableValues['id'];
                        $modelVariableValues->update();
                    }
                }else{                    
                    $customerServiceVlan    =    false;
                }
            }else{
                $customerServiceVlan    =     $variableValues['value1'];
            }
        return $customerServiceVlan;       
    }
    
    /*
     * VRF Naming & BGP Address Family Convention
     * Following table depicts the format for VRF Name to be used for Internal & External customers. This would be used for all L3VPN services.
     * Internal/External Customers 
     * ----------------------------------------------------------------------------------------------------------
     * VRF Name Format                                                                     E.g.
     * ---------------------------------------------------------------------------------------------------------- 
     * Parent  Services  Company Name  Cust ID/VPN ID                      Hub/Spoke   
     * ----------------------------------------------------------------------------------------------------------
     * RJIL    ENT       RIL           10 digit CustID OR 12 Digit VPNID   Hub        RJIL-ENT-RIL-PETRO-HUB 
     * RJIL    ENT       HDFC          10 digit CustID OR 12 Digit VPNID   Spoke      RJIL-ENT-HDFC-FINANCE-SPOKE 
     */
//    public function getVrfName($parentcompany = "RJIL",$ent ="ENT",$companyname="RIL",$customer_id="1234567891",$hub = "hub/spoke"){
    public static function getVrfName($service_model_template_id, $resource_manager_id, $customer_id){  
//        $vrfName = $parentcompany."-".$ent."-".$companyname."-".$customer_id."-".$hub;
        $customerModel  = Customer::find()->where(['id'=>$customer_id])->one();
        $providerModel  = Provider::find()->where(['id'=>$customerModel['provider_id']])->one();
        $serviceModelTemplate   = ServiceModelTemplate::find()->where(['id'=>$service_model_template_id])->one();        
        $serviceModel   = ServiceModel::find()->where(['id'=>$serviceModelTemplate['service_model_id']])->one();
        $services   =    Services::find()->where(['id'=>$serviceModel['service_id']])->one();
        $topologyModel  = Topology::find()->where(['id'=>$serviceModel['topology_id']])->one();
        //$vrfName   =    $providerModel['company_name']."-".$services['name']."-".$customerModel['company_name']."-".$customerModel['customer_id']."-".$topologyModel['name'];
        // change of logic as removed topology name as per network team
        //$vrfName   =    $providerModel['company_name']."-".$services['name']."-".$customerModel['company_name']."-".$customerModel['customer_id'];
        $vrfName   =    "RJIL-ENT-".$customerModel['company_name']."-".$customerModel['customer_id'];
        return $vrfName;
    }
    
    /*
     * Route Distinguisher Standardization
     * Format - <IPv4 Loopback address > :< VPN Identifier> 
     * VPN Customer              VPN Identifier Range 
     * Enterprise(Hub & Spoke)  20101 – 21000 
     * Enterprise(Any to Any)   10201 - 13000 
     */
    public static function getRD($service_model_template_id, $device_id, $resource_manager_id, $customer_id)
    {
        $variablesMapping   = VariablesMapping::find()->where(['variable_id'=>$resource_manager_id,'customer_id'=>$customer_id])->one();
        $variableValues     = VariablesValues::find()->where(['variable_mapping_id'=>$variablesMapping['id'],'is_completed'=>0])->one();
        $deviceModel    = Device::find()->where(['id'=>$device_id])->one();
        $loopback   =   $deviceModel['loopback'];
        $query   = DeviceVariablesMapping::find()->where(['and',['service_model_template_id' => $service_model_template_id, 'device_id'=> $device_id, 'resource_manager_id'=>$resource_manager_id]])->orderBy(['id'=>SORT_DESC]);
        $data   =   $query->asArray()->one(); 
        $rd = explode(":", $data['value']);
        if($data){                
                $rdValue   =   (int)$rd[1];
                $min    =   (int)$variableValues['value1'];
                $max    =   (int)$variableValues['value2'];
                if($rdValue >= $min && $rdValue < $max){
                    $rdvalueRange    =    $rdValue+1;
                    if($rdvalueRange   ==  $max){
                        $modelVariableValues   = new VariablesValues();
                        $modelVariableValues->is_completed  =   1;
                        $modelVariableValues->id    =   $variableValues['id'];
                        $modelVariableValues->update();
                    }
                }else{
                    $rdvalueRange    =    false;
                }
            }else{
                $rdvalueRange    =     $variableValues['value1'];
            }               
            if($rdvalueRange){
                $rdValue    =   $loopback.":".$rdvalueRange;
            }else{
                $rdValue    =    false;
            }
            
        return $rdValue;
    }
    
    /*
     * Route Target Standardization
     * Format - <AS number> :< Identifier> 
     * Identifier would be 10 Digit number with the below identification 
     * First Digit: - Would be used as per the below information. 
     * ---------------------------   
     * Value       Identifier 
     * --------------------------- 
     * 1       External Any to Any 
     * 2       External Hub Site 
     * 3       External Spoke Site 
     * 5       Internal Any to Any 
     * 6       Internal HUB Site 
     * 7       Internal Spoke Site 
     * 
     * Second Digit 
     * ---------------------------------------------------------------------------------
     * Value    Identfier         RT to be reserved for HUB    RT to be reserved for Spoke
     * ---------------------------------------------------------------------------------
     *   1       Major Customer    10                          1000 
     *   2       Medium Customer   5                           500 
     *   3       SMB               2                           250 
     * 
     * Third Digit Onwards till Tenth Digit would be the RT Numbering    00000000 to 99999999 
     * 
     * Example : HDFC HUB Site First Customer   :- 64771:2100000001    reserved till  64771:2100000010 
     *           HDFC Spoke Site First Customer :- 64771:3100000001    reserved till  64771:3100001000 
     */
    public static function getRT($custome_id, $qos, $topology_type = "Hub", $asNumber = "55836") {
        $isInternal = false;
        $customerName = Customer::getCustomerName($custome_id);
        if ((strpos($customerName, 'Reliance') !== false) || (strpos($customerName, 'RJIL') !== false) || (strpos($customerName, 'RIL') !== false)) {
            $isInternal = true;
        } 
        
        if($isInternal) {
            $firstDigit = ($topology_type == "Hub")?6:(($topology_type == "Spoke")?7:5);
        } else {
            $firstDigit = ($topology_type == "Hub")?2:(($topology_type == "Spoke")?3:1);
        }
        
        $secondDigit   = ($qos == "major")?1:(($qos == "medium")?2:3);
        $hubReserved   = ($qos == "major")?10:(($qos == "medium")?5:2);
        $spokeReserved = ($qos == "major")?1000:(($qos == "medium")?500:250);
        
        $rtRange  = RtRange::find()->where(['customer_id' => $custome_id, "topology" => $topology_type, "identifier" => $firstDigit . $secondDigit, "is_full" => 0, "is_active" => 1])->One();

        if ($rtRange)
        {
            $rtRangeModel = RtRange::getRtRange($rtRange['id']);
            if ($rtRange["last_used"] < $rtRange["end_value"])
            {
                $rtValue = $rtRange["last_used"] + 1;
                $rtRangeModel->last_used = $rtValue;
            } else if ($rtRange["last_used"] == $rtRange["end_value"]) {
                $rtRangeModel->is_full = 1;
                $start = $rtValue = $rtRange["end_value"] + 1;
                if($topology_type == "Hub") {
                    $end   = $rtRange["end_value"] + $hubReserved;
                } else if ($topology_type == "Spoke") {
                    $end   = $rtRange["end_value"] + $spokeReserved;
                }
                self::insertRtRange($custome_id, $topology_type, $firstDigit, $secondDigit, $start, $end, $rtValue);
            }
            $rtRangeModel->save(false);
        } else {
            $rtRange  = RtRange::find()->where(["topology" => $topology_type, "identifier" => $firstDigit . $secondDigit, "is_full" => 0, "is_active" => 1])->One();
            if($rtRange)
            {
                $start = $rtValue = (string)$rtRange["end_value"] + 1;
                if($topology_type == "Hub") {
                    $end   = $rtRange["end_value"] + $hubReserved;
                } else if ($topology_type == "Spoke") {
                    $end   = $rtRange["end_value"] + $spokeReserved;
                }
                self::insertRtRange($custome_id, $topology_type, $firstDigit, $secondDigit, $start, $end, $rtValue);
            } else {
                $start = $rtValue = (string)($firstDigit . $secondDigit . "00000001");
                if($topology_type == "Hub") {
                    $end   = ($firstDigit . $secondDigit . "00000000") + $hubReserved;
                } else if ($topology_type == "Spoke") {
                    $end   = ($firstDigit . $secondDigit . "00000000") + $spokeReserved;
                }
                self::insertRtRange($custome_id, $topology_type, $firstDigit, $secondDigit, $start, $end, $rtValue);
            }
        }
        // pe($rtValue);
        // $numberofdigits=10;
        // $rtValue = str_pad($rtValue,$numberofdigits,'0',STR_PAD_RIGHT);        
        // $finalRtValue = $asNumber . ":" . $rtValue;
        return $asNumber . ":" .$rtValue;
    }
    
    public static function insertRtRange($custome_id, $topology_type, $firstDigit, $secondDigit, $start, $end, $rtValue) {
        $rtRangeModel = new RtRange();
        $rtRangeModel->customer_id = $custome_id;
        $rtRangeModel->topology = $topology_type;
        $rtRangeModel->identifier = $firstDigit . $secondDigit;
        $rtRangeModel->start_value = $start;
        $rtRangeModel->end_value = $end;
        $rtRangeModel->last_used = $rtValue;
        $rtRangeModel->save(false);
        return true;
    }
    
    public static function insertDeviceVariableMapping($service_instance_id, $service_model_template_id, $device_id, $systemDefinedValues) {
        foreach($systemDefinedValues as $resource_manager_id => $value)
        {
            $deviceValueModel = new DeviceVariablesMapping();
            $deviceValueModel->service_model_template_id = $service_model_template_id;
            $deviceValueModel->device_id = $device_id;
            $deviceValueModel->resource_manager_id = $resource_manager_id;
            $deviceValueModel->value = is_array($value)?serialize($value):$value;
            $deviceValueModel->save(false);
        }
        return true;
    }
    /**
     * P2P Cross connect Naming Standardization
     * VC-ID Identifier Range 100001 to 200000
     * Internal/External Customers
     * P2P Cross Connect Name Format
     * ----------------------------------------------------------------------
     * Parent   Services    Company Name    Division    VC-ID       Eg
     * ----------------------------------------------------------------------
     * RJIL     ENT         RIL             Petro       100002      RJIL-ENT-RIL-PETRO-100002
     * RJIL     ENT         RIL             IT          100051      RJIL-ENT-RIL-IT-100051
     * ----------------------------------------------------------------------        
     *  function call:  getP2PCrossConnect($service_model_template_id, $device_id, $resource_manager_id,$customer_id,$division='IT') e.g. xconnect(1);   
     */
    public static function getP2PCrossConnect($service_model_template_id, $device_id, $resource_manager_id,$customer_id,$division='IT'){
        $customerModel  = Customer::find()->where(['id'=>$customer_id])->one();
        $providerModel  = Provider::find()->where(['id'=>$customerModel['provider_id']])->one();
        $vcIDMin    =   100001;
        $vcIDMax    =   200000;
        $services   =   "ENT";
        $query   = DeviceVariablesMapping::find()->where(['and',['service_model_template_id' => $service_model_template_id, 'device_id'=> $device_id, 'resource_manager_id'=>$resource_manager_id]])->orderBy(['id'=>SORT_DESC]);
        $data   =   $query->asArray()->one(); 
        if(!empty($data['value'])){
            $xconnectvalue  =   explode("-",$data['value']);            
            $vcID   =   (int)$xconnectvalue[4]+1;            
        }else{
            $vcID   =   $vcIDMin;
        }
        $p2pCrossValue  =   "RJIL"."-".$services."-".$customerModel['company_name']."-".$division."-".$vcID;
        return $p2pCrossValue;
    }

    /**
     * Bridge Group Name Standardization
     * VC-ID Identifier Range 100001 to 200000 -- No VC- D in both Bridge domain & Bridge Group
     * Internal/External Customers
     * P2P Cross Connect Name Format
     * ----------------------------------------------------------------------
     * Parent   Services    Company Name    Division    VC-ID       Eg
     * ----------------------------------------------------------------------
     * RJIL     ENT         RIL             Petro       100002      RJIL-ENT-RIL-PETRO
     * RJIL     ENT         RIL             IT          100051      RJIL-ENT-RIL-IT
     * ----------------------------------------------------------------------        
     *  function call:  getP2PCrossConnect($service_model_template_id, $device_id, $resource_manager_id,$customer_id,$division='IT') e.g. xconnect(1);   
     */
    public static function getBridgeGroupName($service_model_template_id, $device_id, $resource_manager_id,$customer_id,$division='IT'){
        $customerModel  = Customer::find()->where(['id'=>$customer_id])->one();
        $providerModel  = Provider::find()->where(['id'=>$customerModel['provider_id']])->one();
        $services   =   "ENT";       
        $bridgeGrpName  =   "RJIL"."-".$services."-".$customerModel['company_name']."-".$division;
        return $bridgeGrpName;
    }
    /**
     * VPN ID 
     * Range between VPNID: 100001 to 200000
     * @param type $service_model_template_id
     * @param type $device_id
     * @param type $resource_manager_id
     * @param type $customer_id
     */
    public static function getVPNID($service_model_template_id, $device_id,$resource_manager_id,$customer_id){
        $variablesMapping   = VariablesMapping::find()->where(['variable_id'=>$resource_manager_id,'customer_id'=>$customer_id])->one();
        $variableValues     = VariablesValues::find()->where(['variable_mapping_id'=>$variablesMapping['id'],'is_completed'=>0])->one();
        $min    =   $variableValues['value1'];
        $max    =   $variableValues['value2'];
        $query   = DeviceVariablesMapping::find()->where(['and',['service_model_template_id' => $service_model_template_id, 'device_id'=> $device_id, 'resource_manager_id'=>$resource_manager_id]])->orderBy(['id'=>SORT_DESC]);
        $data   =   $query->asArray()->one(); 
        if(!empty($data['value'])){
            $vpnIDValue =   (int)$data['value']+1;
        }else{
            $vpnIDValue =   $min;
        }
        return $vpnIDValue;
    }
    
    /**
     * VPN ID 
     * Range between ASN: 64512 to 65535
     * @param type $service_model_template_id
     * @param type $device_id
     * @param type $resource_manager_id
     * @param type $customer_id
     */
    public static function asn($service_model_template_id, $device_id,$resource_manager_id,$customer_id){
        $variablesMapping   = VariablesMapping::find()->where(['variable_id'=>$resource_manager_id,'customer_id'=>$customer_id])->one();
        $variableValues     = VariablesValues::find()->where(['variable_mapping_id'=>$variablesMapping['id'],'is_completed'=>0])->one();
        $min    =   $variableValues['value1'];
        $max    =   $variableValues['value2'];
        $query   = DeviceVariablesMapping::find()->where(['and',['service_model_template_id' => $service_model_template_id, 'device_id'=> $device_id, 'resource_manager_id'=>$resource_manager_id]])->orderBy(['id'=>SORT_DESC]);
        $data   =   $query->asArray()->one(); 
        if(!empty($data['value'])){
            $asnValue =   (int)$data['value']+1;
        }else{
            $asnValue =   $min;
        }
        return $asnValue;
    }
    
    /**
     * egress-bridge-port-no
     * @param type $device array
     * To derive a egress_bridge port no using the ingress_port_no which is user_defined value
     */
    public static function egress_bridge_port_no($device){
        return false;
        if($device['ingress_port_no']){
            return "1 ".$device['ingress_port_no'];
        }
        return false;
    }
    public function rpl_owerner(){
        // Secondray port towards DEMARK will be pass 
    }

    public function pbx_phy_intf(){
        //get interface from prashant if present else assign new interfcase and sub interface  
        $subinterface= $interface. ".".$vlan; // from above function vlan_cust
    }
    
    /*Relation with Customer*/
    public function getCustomer($customer_id){
        return $this->hasOne(Customer::className(),["id"=>$customer_id]);
    }

    /*Relation with Data Type*/
    public function getDataType(){
        return $this->hasOne(DataType::className(),["id"=>"data_type_id"]);
    }
    
    public static function getIpAddressMappedVariables($service_template_id,$ip_type) {
        $fieldArr = array();
        $query  = new Query;
        $query->select("`tbl_variables_master`.`id` as 'id',`tbl_data_type`.`data_type` as 'data_type',`tbl_variables_master`.`variable_name` as 'variable_name'")
                ->from('tbl_service_model_template_variables')
                ->join('inner join', 'tbl_variables_master', 'tbl_service_model_template_variables.resource_manager_id=tbl_variables_master.id')
                ->join('inner join', 'tbl_data_type', 'tbl_variables_master.data_type_id=tbl_data_type.id');
        $query->where(['tbl_service_model_template_variables.template_id' =>  $service_template_id,'tbl_data_type.data_type' =>  [$ip_type]]);
        $data  =  $query->all();
        foreach($data as $key => $value) {
            $fieldArr[$value['id']] = $value['variable_name'];
        }
        return $fieldArr;
    }
    
    public static function getSystemDefinedVariablesArray($device_role_id, $device_id, $service_model_id, $customer_id, $service_instance_id, $device) {  

        $systemDefinedValue = array();
        $systemDefinedKey = array();
        //$ipVariables    =   ServiceModelTemplateVariables::getIpAddressMappedVariables($service_model_id);
        $serviceTemplate = ServiceModelTemplate::find()
                ->where(['service_model_id' => $service_model_id, 'device_role_id' => $device_role_id])
                ->One();
        $serviceVariables = ServiceModelTemplateVariables::find()
                ->where(['template_id' => $serviceTemplate['id']])
                ->All();
        $serviceModel   = ServiceModel::find()->where(['id' => $service_model_id])->one();
		$subService 	= SubServices::getSubServiceById($serviceModel->sub_service_id);
		
		$sub_service_id = $subService["ref_id"];
		$managed = ($subService["is_managed"])?"managed":"unmanaged";
		
        $deviceModel    = Device::find()->where(['id' =>  $device_id])->one();
        $hostname     = $deviceModel['hostname'];//AMWDAMWDESR009
		//	 hostname": "MUMBRLB2ESR003",
		//$neighbors_hostname = "MUMBRLB1ESR004";
        $neighbors_hostname = self::getTopologyParams($hostname);
        $interface = self::getNeibhourHost($hostname, $neighbors_hostname);
        $topologyModel  = Topology::find()->where(['id' => $serviceModel['topology_id']])->one();

        //$interface      = "GigabitEthernet0/0/5";
        $vlanValue      = "";
        $interfaceValue	= "";

        // IPV6 : Get ipv6 variables values
        $ipv6_addresses = array();
        $ipv6Variables = self::getIpAddressMappedVariables($serviceTemplate['id'],'ipv6');
        if(count($ipv6Variables) > 0) {
            $resulIpv6 = Ipv6PoolAssignment::getIpv6PoolAssignment($service_instance_id, $serviceTemplate['id'], $device_id, 6, $ipv6Variables);
		
			
		if(!empty($resulIpv6))
            {
                foreach($ipv6Variables as $ikey => $ivalue) {
                    if($ivalue == 'ipv6_address1') {
                        $ipv6_addresses[$ivalue] = str_replace('2405', 'FE80', $resulIpv6['ipv6_address2']);
                    } else {
                        if($ivalue == 'vipv6_address' || $ivalue	==	'relay_destination') {
                            $ipv6_addresses[$ivalue] = $resulIpv6[$ivalue];
                        } else {
                            $ipv6_addresses[$ivalue] = $resulIpv6[$ivalue] . '/' . $resulIpv6['subnet'];
                        }
                    }
                }
            }
        }
		
         // IPV4 : Get ipv4 variables values
        $ipv4_addresses = [];
        $ipv4Variables = self::getIpAddressMappedVariables($serviceTemplate['id'],'ipv4');
        if($ipv4Variables) {
          
            $ipv4Assignment = new Ipv4Assignment();
            $resultIpv4 =  $ipv4Assignment->asssignIp(
                $service_instance_id, 
                $serviceTemplate['id'], 
                 $device_id,
                 6, 
                $ipv4Variables
            );
		
          
            if($resultIpv4){
                foreach($ipv4Variables as $ikey => $ivalue) {
                    if(isset($resultIpv4[$ivalue])) {
                        $ipv4_addresses[$ivalue] = $resultIpv4[$ivalue];
                    } 
                }
            }
        }
        
	   $vrrpValue      = rand(1,20);
        if (count($serviceVariables) > 0) {
            foreach ($serviceVariables as $value) {
                $resourceVariables = VariablesMaster::find()
                        ->where(['id' => $value->resource_manager_id, 'type' => 'system'])
                        ->One();
                if (isset($resourceVariables['type']) && $resourceVariables['type'] == 'system') {
					$variable = $resourceVariables['variable_name'];
					
                    if (!empty($value->resource_manager_id)) {
					
                        // $systemDefinedValue[$resourceVariables['variable_name']] = self::getSystemDefinedRange($value->resource_manager_id);
                        // $systemDefinedKey[$resourceVariables['id']] = self::getSystemDefinedRange($value->resource_manager_id);
                        if ($resourceVariables['variable_name'] == 'rd') {
                            $rdValue = self::getRD($serviceTemplate['id'], $device_id, $value->resource_manager_id, $customer_id);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $rdValue;
                        }

                        if (strstr($resourceVariables['variable_name'], 'vrf-name')) {

                            $vrfValue = self::getVrfName($serviceTemplate['id'], $value->resource_manager_id, $customer_id);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $vrfValue;
                            $systemDefinedKey[$resourceVariables['id']] = $vrfValue;
                        }

                        if (strstr($resourceVariables['variable_name'],'cust-vlan')) {
                          
                            if (empty($vlanValue)) {
                                $vlanValue = self::getCustomerServiceVlan($serviceTemplate['id'], $device_id, $value->resource_manager_id, $customer_id);
                            }
                            $systemDefinedValue[$resourceVariables['variable_name']] = $vlanValue;
                            $systemDefinedKey[$resourceVariables['id']] = $vlanValue;
                        }
                        if (strstr($resourceVariables['variable_name'], 'int_desc')) {
                            if (empty($interfaceValue)) {
                                $interfaceValue = self::getInterfaceDescription($service_instance_id, 100, "managed");
                            }
                            $systemDefinedValue[$resourceVariables['variable_name']] = $interfaceValue;
                            $systemDefinedKey[$resourceVariables['id']] = $interfaceValue;
                        }
                        if ($resourceVariables['variable_name'] == 'rt') {
                            $rdValue = self::getRT($customer_id, "major", $topologyModel['name']);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $rdValue;
                            $systemDefinedKey[$resourceVariables['id']] = $rdValue;
                        }
                        if (strstr($resourceVariables['variable_name'], 'p2p_cross')) {
                            $p2pcrossValue = self::getP2PCrossConnect($serviceTemplate['id'], $device_id, $value->resource_manager_id, $customer_id);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $p2pcrossValue;
                            $systemDefinedKey[$resourceVariables['id']] = $p2pcrossValue;
                        }
                        if (strstr($resourceVariables['variable_name'], 'vpn-id')) {
                            $vpnIDValue = self::getVPNID($serviceTemplate['id'], $device_id, $value->resource_manager_id, $customer_id);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $vpnIDValue;
                            $systemDefinedKey[$resourceVariables['id']] = $vpnIDValue;
                        }
                        if (in_array($resourceVariables['variable_name'], array("bri-dom-name", "bri-grp-name", "vfi_name", "pw-class"))) {
                            $systemDefinedValue[$resourceVariables['variable_name']] = self::getP2PCrossConnect($serviceTemplate['id'], $device_id, $value->resource_manager_id, $customer_id);
                            $systemDefinedKey[$resourceVariables['id']] = self::getP2PCrossConnect($serviceTemplate['id'], $device_id, $value->resource_manager_id, $customer_id);
                        }
                        if (in_array($resourceVariables['variable_name'], array('bridge_domain_id', 'bvi-no'))) {
                            if (empty($vlanValue)) {
                                $vlanValue = self::getCustomerServiceVlan($serviceTemplate['id'], $device_id, $value->resource_manager_id, $customer_id);
                            }
                            $systemDefinedValue[$resourceVariables['variable_name']] = $vlanValue;
                            $systemDefinedKey[$resourceVariables['id']] = $vlanValue;
                        }
                        if ($resourceVariables['variable_name'] == 'vrrp_group_no') {
                            $systemDefinedValue[$resourceVariables['variable_name']] = $vrrpValue;
                            $systemDefinedKey[$resourceVariables['id']] = $vrrpValue;
                        }
                        if ($resourceVariables['variable_name'] == 'desc') {
                            if (empty($interfaceValue)) {
                                $interfaceValue = self::getInterfaceDescription($service_instance_id, 100, "managed");
                            }
                            $systemDefinedValue[$resourceVariables['variable_name']] = $interfaceValue;
                            $systemDefinedKey[$resourceVariables['id']] = $interfaceValue;
                        }
                        if ($resourceVariables['variable_name'] == 'ring-no') {
                            $ringNo = self::ring_no($hostname);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $ringNo;
                            $systemDefinedKey[$resourceVariables['id']] = $ringNo;
                        }
                        if (in_array($variable_name, ['erps-desc'])) {
                            $descriptionErps="";
                            if (isset($ringNo)) {
                            $descriptionErps = self::description_erps($ringNo);    
                            $systemDefinedValue[$resourceVariables['variable_name']] = $descriptionErps;
                            $systemDefinedKey[$resourceVariables['id']] = $descriptionErps;
                            }   
                        }
                        if ($resourceVariables['variable_name'] == 'enc_vlan') {
                            if (empty($vlanValue)) {
                                $vlanValue = self::getCustomerServiceVlan($serviceTemplate['id'], $device_id, $value->resource_manager_id, $customer_id);
                            }
                            $enc_vlan = self::enc_vlan($hostname, $hostname, $vlanValue);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $enc_vlan;
                            $systemDefinedKey[$resourceVariables['id']] = $enc_vlan;
                        }
                        if ($resourceVariables['variable_name'] == 'exclusion-list') {
                            $exclusion_vlan = self::exclusion_vlan($hostname, $neighbors_hostname, $vlanValue);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $exclusion_vlan;
                            $systemDefinedKey[$resourceVariables['id']] = $exclusion_vlan;
                        }
                        if ($resourceVariables['variable_name'] == 'intf_facing_ecr') {
                            $intf_facing_ecr = self::intf_facing_ecr($hostname, $neighbors_hostname, $vlanValue);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $intf_facing_ecr;
                            $systemDefinedKey[$resourceVariables['id']] = $intf_facing_ecr;
                        }
                        if ($resourceVariables['variable_name'] == 'erps-inst-no') {
                            $erps_inst_no = self::erps_inst_no($hostname);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $erps_inst_no;
                            $systemDefinedKey[$resourceVariables['id']] = $erps_inst_no;
                        }
                        if ($resourceVariables['variable_name'] == 'erps_inst_no_1') {
                            $erps_inst_no_1 = self::erps_inst_no_1($hostname);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $erps_inst_no_1;
                            $systemDefinedKey[$resourceVariables['id']] = $erps_inst_no_1;
                        }
                        if ($resourceVariables['variable_name'] == 'erps_inst_no_2') {
                            $erps_inst_no_2 = self::erps_inst_no_2($hostname);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $erps_inst_no_2;
                            $systemDefinedKey[$resourceVariables['id']] = $erps_inst_no_2;
                        }
                        if ($resourceVariables['variable_name'] == 'rpl-port-owner') {
                            $rpl_owner = self::rpl_owner($hostname, $hostname);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $rpl_owner;
                            $systemDefinedKey[$resourceVariables['id']] = $rpl_owner;
                        }
                        if ($resourceVariables['variable_name'] == 'raps-vlan') {
                            $vlan_raps = self::vlan_raps($hostname);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $vlan_raps;
                            $systemDefinedKey[$resourceVariables['id']] = $vlan_raps;
                        }
                        if ($resourceVariables['variable_name'] == 'service_inst') {
                            $service_inst = self::service_inst($hostname, $interface);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $service_inst;
                            $systemDefinedKey[$resourceVariables['id']] = $service_inst;
                        }
                        if ($resourceVariables['variable_name'] == 'trunk-no') {
                            $trunk_no = self::trunk_no($hostname, $interface);
							pe($trunk_no);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $trunk_no;
                            $systemDefinedKey[$resourceVariables['id']] = $trunk_no;
                        }
                        if ($resourceVariables['variable_name'] == 'service-inst-demarc') {
                            if (isset($ringNo)) {
                            $service_inst_demarc = self::service_inst_demarc($ringNo);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $service_inst_demarc;
                            $systemDefinedKey[$resourceVariables['id']] = $service_inst_demarc;
                            }
                        }
                        if ($resourceVariables['variable_name'] == 'asn') {
                            $asn = self::asn($serviceTemplate['id'], $device_id, $value->resource_manager_id, $customer_id);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $asn;
                            $systemDefinedKey[$resourceVariables['id']] = $asn;
                        }
                        if ($resourceVariables['variable_name'] == 'dfp_group_no') {
                            $systemDefinedValue[$resourceVariables['variable_name']] = $vrrpValue;
                            $systemDefinedKey[$resourceVariables['id']] = $vrrpValue;
                        }
                        if ($resourceVariables['variable_name'] == 'customer_vlan4') {
                            $vlan4 = self::getCustomerServiceVlan($serviceTemplate['id'], $device_id, $value->resource_manager_id, $customer_id);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $vlan4;
                            $systemDefinedKey[$resourceVariables['id']] = $vlan4;
                        }
                        if ($resourceVariables['variable_name'] == 'customer_vlan6') {
                            $vlan6 = $vlan4 + 1;
                            $systemDefinedValue[$resourceVariables['variable_name']] = $vlan6;
                            $systemDefinedKey[$resourceVariables['id']] = $vlan6;
                        }
                        if ($resourceVariables['variable_name'] == 'egress-bridge-port-no') {
                            $egressValue = self::egress_bridge_port_no($device);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $egressValue;
                            $systemDefinedKey[$resourceVariables['id']] = $egressValue;
                        }
                        if ($resourceVariables['variable_name'] == 'encapsulaton_vlans') {
                            $encapsulaton_vlans = self::encapsulaton_vlans($hostname, $interface);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $encapsulaton_vlans;
                            $systemDefinedKey[$resourceVariables['id']] = $encapsulaton_vlans;
                        }

                        if ($resourceVariables['variable_name'] == 'customer_name') {
                            $customer_name = Customer::getCustomerName($customer_id);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $customer_name;
                            $systemDefinedKey[$resourceVariables['id']] = $customer_name;
                        }
                        if ($resourceVariables['variable_name'] == 'flow_name') {
                            if (empty($vlanValue)) {
                                $vlanValue = self::getCustomerServiceVlan($serviceTemplate['id'], $device_id, $value->resource_manager_id, $customer_id);
                            }
                            $systemDefinedValue[$resourceVariables['variable_name']] = $vlanValue;
                            $systemDefinedKey[$resourceVariables['id']] = $vlanValue;
                        }
						
						// IPV6
						if(in_array($variable,["nei-ce-ip-ipv6","ipv6-wan-address","ipv6-address"])){
							  $systemDefinedValue[$resourceVariables['variable_name']] = $ipv6_addresses[$resourceVariables['variable_name']];
                              $systemDefinedKey[$resourceVariables['id']] = $ipv6_addresses[$resourceVariables['variable_name']];
						}
						if ($resourceVariables['variable_name'] == 'ipv6_address1') {
                            $systemDefinedValue[$resourceVariables['variable_name']] = $ipv6_addresses[$resourceVariables['variable_name']];
                            $systemDefinedKey[$resourceVariables['id']] = $ipv6_addresses[$resourceVariables['variable_name']];
                        }
						
                        if ($resourceVariables['variable_name'] == 'ipv6_address1') {
                            $systemDefinedValue[$resourceVariables['variable_name']] = $ipv6_addresses[$resourceVariables['variable_name']];
                            $systemDefinedKey[$resourceVariables['id']] = $ipv6_addresses[$resourceVariables['variable_name']];
                        }
                        if ($resourceVariables['variable_name'] == 'ipv6_address2') {
                            $systemDefinedValue[$resourceVariables['variable_name']] = $ipv6_addresses[$resourceVariables['variable_name']];
                            $systemDefinedKey[$resourceVariables['id']] = $ipv6_addresses[$resourceVariables['variable_name']];
                        }
                        if ($resourceVariables['variable_name'] == 'ipv6_wan_address') {
                            $systemDefinedValue[$resourceVariables['variable_name']] = isset($ipv6_addresses[$resourceVariables['variable_name']]) ? $ipv6_addresses[$resourceVariables['variable_name']] : "" ;
                            $systemDefinedKey[$resourceVariables['id']] = isset($ipv6_addresses[$resourceVariables['variable_name']]) ? $ipv6_addresses[$resourceVariables['variable_name']] : "";
                        }

                        if ($resourceVariables['variable_name'] == 'vip_primary') {
                            $systemDefinedValue[$resourceVariables['variable_name']] = $ipv4_addresses[$resourceVariables['variable_name']];
                            $systemDefinedKey[$resourceVariables['id']] = $ipv4_addresses[$resourceVariables['variable_name']];
                        }

                        if ($resourceVariables['variable_name'] == 'vip_secondary') {
                            $systemDefinedValue[$resourceVariables['variable_name']] = $ipv4_addresses[$resourceVariables['variable_name']];
                            $systemDefinedKey[$resourceVariables['id']] = $ipv4_addresses[$resourceVariables['variable_name']];
                        }

                        if (in_array($resourceVariables['variable_name'] ,[ 'ipv4-wan-address','nei-ce-ip-ipv4','nei-ip','ipv4-address','neighbor','ce-intf-ip','nei-ag2-ip','bvi-nei-ip']) ){
                            $systemDefinedValue[$resourceVariables['variable_name']] = $ipv4_addresses[$resourceVariables['variable_name']];
                            $systemDefinedKey[$resourceVariables['id']] = $ipv4_addresses[$resourceVariables['variable_name']];
                        }
						
						if ($resourceVariables['variable_name'] == 'vipv4_address') {
                            $systemDefinedValue[$resourceVariables['variable_name']] = $ipv4_addresses[$resourceVariables['variable_name']];
                            $systemDefinedKey[$resourceVariables['id']] = $ipv4_addresses[$resourceVariables['variable_name']];
                        }
						if ($resourceVariables['variable_name'] == 'vipv6_address') {
                            $systemDefinedValue[$resourceVariables['variable_name']] = $ipv6_addresses[$resourceVariables['variable_name']];
                            $systemDefinedKey[$resourceVariables['id']] = $ipv6_addresses[$resourceVariables['variable_name']];
                        }
						if ($resourceVariables['variable_name'] == 'dhcp_helper_address_1') {
                            $systemDefinedValue[$resourceVariables['variable_name']] = $ipv4_addresses[$resourceVariables['variable_name']];
                            $systemDefinedKey[$resourceVariables['id']] = $ipv4_addresses[$resourceVariables['variable_name']];
                        }
						if ($resourceVariables['variable_name'] == 'dhcp_helper_address_2') {
                            $systemDefinedValue[$resourceVariables['variable_name']] = $ipv4_addresses[$resourceVariables['variable_name']];
                            $systemDefinedKey[$resourceVariables['id']] = $ipv4_addresses[$resourceVariables['variable_name']];
                        }
						if ($resourceVariables['variable_name'] == 'relay_destination') {
                            $systemDefinedValue[$resourceVariables['variable_name']] = $ipv6_addresses[$resourceVariables['variable_name']];
                            $systemDefinedKey[$resourceVariables['id']] = $ipv6_addresses[$resourceVariables['variable_name']];
                        }  
						if ($resourceVariables['variable_name'] == 'router_id') {
							$router_id = self::router_id($hostname);
                            $systemDefinedValue[$resourceVariables['variable_name']] = $router_id;
                            $systemDefinedKey[$resourceVariables['id']] = $router_id;
                        }
                        if($resourceVariables['variable_name'] == "vrf-name-vlan-cust"){
                             $systemDefinedValue[$resourceVariables['variable_name']] = "";
                             $systemDefinedKey[$resourceVariables['id']] = "";
                        }
                        if($resourceVariables['variable_name'] == "hostname"){
                             $systemDefinedValue[$resourceVariables['variable_name']] = $hostname;
                             $systemDefinedKey[$resourceVariables['id']] = $hostname;
                        }
						 if(in_array($resourceVariables['variable_name'],[ "bit-value","block-port-no","remote-as"] )){
							 $variableValue = self::getVariablevalue($value->resource_manager_id,$customer_id);
                             $systemDefinedValue[$resourceVariables['variable_name']] =$variableValue ;
                             $systemDefinedKey[$resourceVariables['id']] = $variableValue ;
                        }
						if($variable == "sub-service-id"){							
							  $systemDefinedValue[$resourceVariables['variable_name']] = $sub_service_id;
                               $systemDefinedKey[$resourceVariables['id']] = $sub_service_id;
						}	
						
						

                    }
                }
            }
        }

        if(isset($systemDefinedValue["vrf-name-vlan-cust"])){
           
             $vrf_name_vlan_cust_arr[] = isset($systemDefinedValue['vrf-name'])?$systemDefinedValue['vrf-name']:"";
             $vrf_name_vlan_cust_arr[] = isset($systemDefinedValue['cust-vlan'])?$systemDefinedValue['cust-vlan']:"";
             $vrf_name_vlan_cust = trim(implode("-",$vrf_name_vlan_cust_arr));
             $systemDefinedValue[$resourceVariables['variable_name']] = $vrf_name_vlan_cust;
             $systemDefinedKey[$resourceVariables['id']] = $vrf_name_vlan_cust;
        }
		
		//pe($systemDefinedValue);
        return array("value" => $systemDefinedValue, "key" => $systemDefinedKey);
    }
	
	public static function getVariablevalue($resource_manager_id,$customer_id){
		$variablesMapping   = VariablesMapping::find()->where(['variable_id'=>$resource_manager_id,'customer_id'=>$customer_id])->one();
		if($variablesMapping){
			$variableValues     = VariablesValues::find()->where(['variable_mapping_id'=>$variablesMapping['id'],'is_completed'=>0])->one();	
			return $variableValues['value1'];
		}
		return false;
	}
	
	/*
	@param : ipType = ipv4/ipv6/both
	*/
	public static function getFttpLp($customerName,$ipType){
		return "RJIL-ENT-VPN-{$customerName}-{$ipType}-IN";
	}

    public static function getRoutePolicy($customerName,$ipType){
        return "RJIL-ENT-VPN-{$customerName}-{$ipType}-IN1";
    }
	
	public static function getVariableValueFromPool($service_model_template_id, $device_id, $resource_manager_id, $customer_id){
	
        $variablesMapping   = VariablesMapping::find()->where(['variable_id'=>$resource_manager_id,'customer_id'=>$customer_id])->one();
        $variableValues     = VariablesValues::find()->where(['variable_mapping_id'=>$variablesMapping['id'],'is_completed'=>0])->one();    
        $data   = DeviceVariablesMapping::find()
					->where([
								'service_model_template_id' => $service_model_template_id, 
								'device_id'=> $device_id, 
								'resource_manager_id'=>$resource_manager_id
							])
					->orderBy(['id'=>SORT_DESC])
					->asArray()->one();
		
		$retValue = false;	
        if($data){                
			$assignedValue   =   $data["value"];
			$min    =   (int)$variableValues['value1'];
			$max    =   (int)$variableValues['value2'];
			if($assignedValue >= $min && $assignedValue < $max){
				$retValue    =    $assignedValue+1;
				if($retValue   ==  $max){
					$modelVariableValues   = new VariablesValues();
					$modelVariableValues->is_completed  =   1;
					$modelVariableValues->id    =   $variableValues['id'];
					$modelVariableValues->update();
				}
			}
		}else{
			$retValue    =   $variableValues['value1'];
		}  		
        return $retValue;
    }
    
}
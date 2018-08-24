<?php

namespace app\models;

use yii\db\ActiveRecord;
use app\models\Subservices;
use app\models\Parameters;
use app\models\Services;
/**
 * @SWG\Definition(
 *   definition="Servicedeploy1",
 *   type="object",
 *   required={"service_order_id", "customer_id","sub_service_id",},
 *       @SWG\Property(property="service_order_id", type="string"),
 *       @SWG\Property(property="customer_id", type="integer"),
 *       @SWG\Property(property="sub_service_id", type="integer"),
 *       @SWG\Property(property="endpoint_device_count", type="integer"), 
 * ),
 * @SWG\Definition(
 *   definition="Servicedeploy2",
 *   type="object",
 *   required={"transaction_id", "device_id","parameters","endpoint_devices",}, 
 *       @SWG\Property(property="transaction_id", type="integer"), 
 *       @SWG\Property(property="endpoint_devices",type="array",
 *		 @SWG\Items(@SWG\Property(property="device_id", type="array",items="String"),
 * 		 @SWG\Property(property="parameters", type="object", @SWG\Property(
 *             property="inner abc",
 *             type="number",
 *             default=1,
 *             example=123
 *           ),@SWG\Property(
 *             property="inner xyz",
 *             type="number",
 *             default=1,
 *             example=2333
 *           )))
 * )),
 *  @SWG\Definition(
 *   definition="Servicedeploy3",
 *   type="object",
 *   required={"transaction_id", "action"}, 
 *       @SWG\Property(property="transaction_id", type="integer"),
 *       @SWG\Property(property="action", type="integer"),
 *       @SWG\Property(property="datetime", type="string",format="date-time"),  
 * ),
 *  @SWG\Definition(
 *   definition="Servicedeploy2Response",
 *   type="object",
 *   required={"transaction_id", "action","status_code","sub_status_code","message","data"}, 
 *       @SWG\Property(property="status_code", type="integer"),
 *       @SWG\Property(property="sub_status_code", type="integer"),
 *       @SWG\Property(property="message", type="string",type="String"),
 *       @SWG\Property(property="data",type="object",@SWG\Property(property="transaction_id", type="integer"))
 * ),
 */
class Servicesdeploy extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%sub_services}}';
    }

    public function rules()
    {
        return [
            [['name', 'alias','description','service_id',], 'required'],
            ['name', 'string', 'max' => 255],
            ['alias', 'string'],
            ['description', 'string'],
            ['service_id', 'int'],
        ];
    }
}
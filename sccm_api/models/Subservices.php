<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @SWG\Definition(
 *   definition="SubServiceWithFilters",
 *   type="object",
 *   required={"status_code","sub_status_code","message","data"},
 *   @SWG\Property(property="status_code", type="integer",example="2011"),
 *   @SWG\Property(property="sub_status_code", type="integer",example="2011"),
 *   @SWG\Property(property="message", type="string",example="Sub Service Data retrieval successfully"),
 *   @SWG\Property(property="data",@SWG\Property(property="sub_service", @SWG\Property(property="sub_service_id",type="integer",example="2.37"),@SWG\Property(property="service_name",type="string",example="Service Name"),@SWG\Property(property="system_define",type="array",@SWG\Items(@SWG\Property(property="hostname", type="integer"),@SWG\Property(property="vpn-id",type="string"),@SWG\Property(property="vrf-name",type="string"))),@SWG\Property(property="user_define",type="array",@SWG\Items(@SWG\Property(property="remote-as", type="integer"),@SWG\Property(property="bit-value",type="string"),@SWG\Property(property="block-port-no",type="string")))))
 * )
 */

class Subservices extends ActiveRecord
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
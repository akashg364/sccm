<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @SWG\Definition(
 *   definition="Subservicefilters1",
 *   type="object",
 *   required={"type", "param","value"},
 *   allOf={
 *       @SWG\Property(property="type", type="string"),
 *       @SWG\Property(property="param", type="string"),
 *       @SWG\Property(property="value", type="string"),
 *       @SWG\Schema(
 *           required={"id"},
 *           @SWG\Property(property="id", format="int64", type="integer")
 *       )
 *   }
 * )
 * 
 * @SWG\Definition(
 *   definition="SubServiceFilters",
 *   type="object",
 *   required={"status_code","sub_status_code","message","data"},
 *   @SWG\Property(property="status_code", type="integer",example="200"),
 *   @SWG\Property(property="sub_status_code", type="integer",example="2000"),
 *   @SWG\Property(property="message", type="string",example="Success"),
 *   @SWG\Property(property="data",@SWG\Property(property="is_managed",@SWG\Property(property="managed", type="integer", format="float", example="1"),@SWG\Property(property="unmanaged",type="string",example="0")),@SWG\Property(property="terminated_at",@SWG\Property(property="css", type="integer", format="float", example="1"),@SWG\Property(property="ecr",type="string",example="2"),@SWG\Property(property="ag1", type="integer", format="float", example="3"),@SWG\Property(property="ag2",type="string",example="4")),@SWG\Property(property="routing_protocol",@SWG\Property(property="bgp", type="integer", format="float", example="1"),@SWG\Property(property="static",type="string",example="2")),@SWG\Property(property="topology_type",@SWG\Property(property="hub_and_spoke", type="integer", format="float", example="1"),@SWG\Property(property="fullmesh",type="string",example="2"),@SWG\Property(property="point_to_point", type="integer", format="float", example="3"),@SWG\Property(property="point_to_multipoint",type="string",example="4"),@SWG\Property(property="multipoint_to_multipoint",type="string",example="5")))
 * )
 */

class Subservicefilters extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%tbl_sub_service_filters}}';
    }

    public function rules()
    {
        return [
            [['type', 'param','value'], 'required'],
            ['type', 'string', 'max' => 255],
            ['param', 'string']
        ];
    }
    	public static function getFilterParam($type,$value){
		$model = self::find()
		->where(["type"=>$type,"value"=>$value])
		->limit(1)->one();
		return $model?$model->param:'';
	}
}
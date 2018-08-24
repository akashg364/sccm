<?php

namespace backend\models;

use Yii;
use backend\models\Component;

/**
 * This is the model class for table "tbl_service_component_reference".
 *
 * @property int $id
 * @property int $service_id
 * @property int $component_id
 * @property int $created_by
 * @property string $created_date
 * @property int $updated_by
 * @property string $updated_date
 */
class ServiceComponentReference extends \common\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'tbl_service_component_reference';
    }

    public function behaviors() {
        return [
            [
                'class' => \yii\behaviors\BlameableBehavior::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['sub_service_id', 'component_id'], 'required'],
            //[['service_id', 'component_id', 'created_by', 'updated_by'], 'integer'],
            [['created_date', 'updated_date', 'created_by', 'updated_by'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'sub_service_id' => 'Service ID',
            'component_id' => 'Component ID',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
            'updated_by' => 'Updated By',
            'updated_date' => 'Updated Date',
        ];
    }

    public static function setServiceComponentReference($components_data, $sub_service_id) {

        if (!empty($components_data) && is_array($components_data) && !empty($sub_service_id)) {
            pr($components_data);
            ServiceComponentReference::deleteAll(['sub_service_id' => $sub_service_id]);

            foreach ($components_data as $componentId => $componentBlocks) {
                if (empty($componentBlocks))
                    continue;
                foreach ($componentBlocks as $block_name) {

                    $serviceComRef = new ServiceComponentReference();
                    $serviceComRef->sub_service_id = $sub_service_id;
                    $serviceComRef->component_block = $block_name;
                    $serviceComRef->component_id = $componentId;
                    $serviceComRef->save();
                }
            }
        }
    }

    public static function getComponents($service_id) {
        $assign_components = [];
        $service_components_ref = ServiceComponentReference::find()
                        ->joinWith(["component"])
                        ->select(['component_id', 'component_block'])
                        ->where(['sub_service_id' => $service_id])
                        ->asArray()->all();
                
        $ret = [];
        if($service_components_ref){
              foreach ($service_components_ref as $component) {
                $ret[$component["component"]["name"]][] = $component["component_block"];
              }
        }
        return $ret;
        // if (!empty($service_components_ref)) {
        //     foreach ($service_components_ref as $service_components_ref_item) {
        //         $component_id = isset($service_components_ref_item['component_id']) ? $service_components_ref_item['component_id'] : '';
        //         if (!empty($component_id)) {

        //             $component_ids[] = $component_id;
        //         }
        //     }
        //     $assign_components = Component::find()->where(['id' => $component_ids])->all();
        // }
        // return $assign_components;
    }

    public static function getComponentBlocks($service_id) {
        $data = ServiceComponentReference::find()
                        ->select(['component_id', 'component_block'])
                        ->where(['sub_service_id' => $service_id])
                        ->asArray()->all();
        $ret = [];
        foreach ($data as $d) {
            $ret[$d["component_id"]][] = $d["component_block"];
        }
        return $ret;
    }

    /* Relation */
    public function getComponent(){
        return $this->hasOne(Component::className(),["id"=>"component_id"]);
    }

}

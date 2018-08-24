<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "device_role".
 *
 * @property string $id
 * @property string $role_name device role name
 * @property int $created_by
 * @property int $modified_by
 * @property string $created_date
 * @property string $modified_date
 * @property string $is_active
 */
class DeviceRole extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_device_role';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['role_name', 'created_by', 'updated_by'], 'required'],
            [['created_by', 'updated_by'], 'integer'],
            [['created_date', 'updated_date'], 'safe'],
            [['is_active'], 'string'],
            ['role_name', 'unique'],
            [['role_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_name' => 'Role Name',
            'created_by' => 'Created By',
            'updated_by' => 'Modified By',
            'created_date' => 'Created Date',
            'updated_date' => 'Modified Date',
            'is_active' => 'Is Active',
        ];
    }

    public static function getDeviceRolesList(){
        $data = self::find()
                ->where(["is_active"=>'1'])
                ->asArray()->all();
        return ArrayHelper::map($data,"id","role_name");     
    }
    
    public static function getDeviceRole($id) {
        if (($deviceRole = DeviceRole::findOne($id)) !== null) {
            return $deviceRole['role_name'];
        }
    }
}

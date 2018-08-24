<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "network_engineers".
 *
 * @property int $id
 * @property string $name
 * @property string $role
 * @property int $status
 */
class NetworkEngineers extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'network_engineers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'role'], 'required'],
            [['id', 'status'], 'integer'],
            [['name', 'role'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'role' => 'Role',
            'status' => 'Status',
        ];
    }
    
    public static function getEngineerList() {
        $data = self::find()->asArray()->all();
        return ArrayHelper::map($data, "id", "name");
    }
    
}
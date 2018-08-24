<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_variables_values".
 *
 * @property int $id
 * @property int $variable_mapping_id
 * @property int $value1
 * @property int $value2
 * @property int $is_completed
 * @property int $created_by
 * @property string $created_date
 * @property int $updated_by
 * @property int $updated_date
 */
class VariablesValues extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_variables_values';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['variable_mapping_id'], 'required'],
            [['variable_mapping_id', 'created_by', 'updated_by','is_completed'], 'integer'],
            [['created_date','value1', 'value2'], 'safe'],
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
            'variable_mapping_id' => 'Variable Mapping ID',
            'value1' => 'Value1',
            'value2' => 'Value2',
            'is_completed' => 'Is Completed',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
            'updated_by' => 'Updated By',
            'updated_date' => 'Updated Date',
        ];
    }
}

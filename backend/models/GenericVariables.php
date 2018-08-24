<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_generic_variables".
 *
 * @property integer $id
 * @property integer $customer_id
 * @property string $variable_name
 * @property string $variable_value
 * @property string $status
 * @property string $created_date
 */
class GenericVariables extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_generic_variables';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'variable_name', 'variable_value'], 'required'],
            [['customer_id'], 'integer'],
            [['status'], 'string'],
            [['created_date'], 'safe'],
            [['variable_name', 'variable_value'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'variable_name' => 'Variable Name',
            'variable_value' => 'Variable Value',
            'status' => 'Status',
            'created_date' => 'Created Date',
        ];
    }
}

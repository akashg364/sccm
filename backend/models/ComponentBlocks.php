<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_component_blocks".
 *
 * @property int $id
 * @property int $component_id
 * @property string $block_name
 */
class ComponentBlocks extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_component_blocks';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['component_id', 'block_name'], 'required'],
            [['component_id'], 'integer'],
            [['block_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'component_id' => 'Component ID',
            'block_name' => 'Block Name',
        ];
    }
    
}

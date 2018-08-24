<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tbl_webhooks".
 *
 * @property int $id
 * @property string $provider
 * @property string $url
 * @property int $created_by
 * @property string $created_date
 * @property int $modified_by
 * @property string $modified_date
 */
class Webhooks extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_webhooks';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'modified_by'], 'integer'],
            [['created_date'], 'required'],
            [['created_date', 'modified_date'], 'safe'],
            [['provider'], 'string', 'max' => 100],
            [['url'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'provider' => 'Provider',
            'url' => 'Url',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
            'modified_by' => 'Modified By',
            'modified_date' => 'Modified Date',
        ];
    }
}

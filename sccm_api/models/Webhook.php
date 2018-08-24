<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @SWG\Definition(
 *   definition="CreateWebhook",
 *   type="object",
 *   required={"client_id", "endpoint"}, 
 *   @SWG\Property(property="client_id", type="integer"),
 *   @SWG\Property(property="endpoint", type="string"),
 * )
 */

class Webhook extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%tbl_webhook}}';
    }

    public function rules()
    {
        return [
            [['client_id', 'endpoint'], 'required'],
            ['endpoint', 'string'],       
            ['client_id', 'integer'],
            [['client_id'], 'unique'],
        ];
    }
}
<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;
use common\components\SccmCommonFunction;

/**
 * This is the model class for table "tbl_api_log".
 *
 * @property int $id Primary Key
 * @property string $api_url
 * @property string $request_method
 * @property string $request
 * @property string $response
 * @property string $log_details
 * @property string $log_file_path Log saved in this file
 * @property string $service_template_id
 * @property string $created_date
 * @property int $created_by
 */
class ApiLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_api_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['request', 'response', 'log_details'], 'string'],
            [['created_date'], 'safe'],
            [['created_by'], 'integer'],
            [['api_url', 'request_method', 'log_file_path', 'service_template_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'api_url' => 'Api Url',
            'request_method' => 'Request Method',
            'request' => 'Request',
            'response' => 'Response',
            'log_details' => 'Log Details',
            'log_file_path' => 'Log File Path',
            'service_template_id' => 'Service Template ID',
            'created_date' => 'Created Date',
            'created_by' => 'Created By',
        ];
    }
    
    public static function saveLog($response) {
        $controller       = Yii::$app->controller->id;
        $action           = Yii::$app->controller->action->id;
        $request_api_name = $controller . "-" . $action;
        $request          = Yii::$app->request->getBodyParams();
        $request_api_url  = Yii::$app->request->getUrl();
        $request_method   = Yii::$app->request->method;
        $request_api_str  = json_encode($request);
        $request_api_log  = new SccmCommonFunction();
        $str_response     = json_encode($response);
        $log_array        = array(
            'api_url'          => $request_api_url, 
            'request_method'   => $request_method, 
            'request'          => $request_api_str, 
            'response'         => $str_response, 
            'request_api_name' => $request_api_name
        );
        $request_api_log->createLog($str_response, $log_array, false);
    }
}

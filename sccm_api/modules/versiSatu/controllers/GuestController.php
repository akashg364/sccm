<?php

namespace app\modules\versiSatu\controllers;

use app\components\Controller;
use app\models\forms\RegisterForm;
use yii\filters\AccessControl;
use app\models\forms\LoginForm;
use Yii;

class GuestController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'login', 'register'],
                        'allow' => true,
                    ],
                ],
            ]
        ];
    }

    /**
     * @SWG\Definition(
     *   definition="About",
     *   type="object",
     *   required={"name", "description", "version", "baseUrl"},
     *   allOf={
     *     @SWG\Schema(
     *       @SWG\Property(property="name", type="string", description="Name App"),
     *       @SWG\Property(property="description", type="string", description="Detail Information App"),
     *       @SWG\Property(property="version", type="string", description="Version APP"),
     *       @SWG\Property(property="baseUrl", type="string", description="Base Url APP")
     *     )
     *   }
     * )
     */
    public function actionIndex() {
        $params = Yii::$app->params;
        return [
            'name' => $params['name'],
            'description' => $params['description'],
            'version' => $params['version'],
            'baseUrl' => $this->baseUrl()
        ];
    }

    /**
     * Login
     *
     * @return mixed
     */
//    public function actionLogin()
//    {
//        $dataRequest['LoginForm'] = Yii::$app->request->post();
//        $model = new LoginForm();
//        if ($model->load($dataRequest) && ($result = $model->login())) {
//            return $this->apiItem($result);
//        }
//
//        return $this->apiValidate($model->errors);
//    }
    public function actionLogin() {
        $dataRequest['LoginForm'] = Yii::$app->request->post();
        $model = new LoginForm();
        $offset = 5 * 60 * 60; //converting 5 hours to seconds.
        $current = gmdate(DATE_ATOM, time() + $offset);
        $expi = strtotime("+60 minutes", time());
        $expiration = gmdate(DATE_ATOM, $expi + $offset);
        $data = array();
        if (empty($dataRequest['LoginForm'])) {
            $data = array('errors' => 'parameter missing');
            return $this->apiResponse(200, 3000, 3001, $data, "parameter missing");
        } else {
            if (empty($dataRequest['LoginForm']['username']) || empty($dataRequest['LoginForm']['password'])) {
                $data = array('errors' => 'parameter value missing');
                return $this->apiResponse(200, 3000, 3002, $data, "parameter value missing");
            }
        }
        if ($model->load($dataRequest) && ($result = $model->login())) {
            $createdDate = gmdate(DATE_ATOM, strtotime($result['attributes']['created_at']));
            $updatedDate = gmdate(DATE_ATOM, strtotime($result['attributes']['updated_at']));
            $data = array(
                "client_id" => $result['attributes']['id'],
                "client_name" => $result['attributes']['username'],
                "username" => $result['attributes']['username'],
                "email" => $result['attributes']['email'],
                "customer_updated_at" => $updatedDate,
                "customer_created_at" => $createdDate,
                "token_created_datetime" => $current,
                "token_expired_datetime" => $expiration,
                "token" => $result['token']
            );

//            return $this->apiResponse($httpstatus, $status, $substatus, $data = array(), $message = '', $error = '');
            return $this->apiResponse(200, 200, 2000, $data, "Data retrieval successfully");
        } else {
            $data = array('errors' => 'Invalid Credential');
            return $this->apiResponse(200, 3000, 3003, $data, "Invalid Credential");
        }
    }

    /**
     * Register
     *
     * @return mixed
     */
    public function actionRegister() {
        $dataRequest['RegisterForm'] = Yii::$app->request->getBodyParams();
        $model = new RegisterForm();
        if ($model->load($dataRequest)) {
            if ($user = $model->register()) {
                return $this->apiCreated($user, 'User created successfully', '1001', '1001');
            }
        }

        return $this->apiValidate($model->errors);
    }

}

<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use backend\models\PasswordResetRequestForm;
use backend\models\ResetPasswordForm;
use \backend\models\ServiceModel;
use \backend\models\Provider;
use \backend\models\Customer;
use \backend\models\ApiLog;
use mdm\admin\models\User;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['request-password-reset', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['reset-password', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                   // 'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionError()
    {
        echo "<pre>terr";exit;
        $exception = Yii::$app->errorHandler->exception;
        if ($exception instanceof \yii\web\NotFoundHttpException) {
            // all non existing controllers+actions will end up here
            return $this->render('pnf'); // page not found
        } else {
            return $this->render('error', ['exception' => $exception]);
        }
    }

    /**
     * Displays homepage Dashboard :: Requested Approval for  and Services.
     *
     * @return string
     */
    public function actionIndex() {
        $modelServices = new ServiceModel();
        $modelProvider = new Provider();
        $modelCustomer = new Customer();

        try {
            $users = array();
            $userId = Yii::$app->user->id;
            $user = User::find()->where(['id' => $userId])->one();

            $services = $modelServices->getAllServicesByUser($userId);

            $userType = isset($user['user_type']) ? $user['user_type'] : "";

            if (strstr($userType, 'provider')) {
                $users = $modelCustomer->getAllCustomersOfProvider($userId);
            }

            if (strstr($userType, 'super')) {
                $users = $modelProvider->getAllProvidersOfAdmin($userId);
            }

            return $this->render('index', [
                'services' => $services,
                'users' => $users,
                'user' => $user]);
        } catch(\Exception $e) {
            ApiLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        try {
            $this->layout = 'ccm-login-layout';
            if (!Yii::$app->user->isGuest) {
                return $this->goHome();
            }

            $model = new LoginForm();
            if ($model->load(Yii::$app->request->post()) && $model->login()) {
                return $this->goBack();
            } else {
                $model->password = '';

                return $this->render('ccm-login', [
                    'model' => $model,
                ]);
            }
        } catch(\Exception $e) {
            ApiLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
    
     /**
     * Request reset password
     * @return string
     */
    public function actionRequestPasswordReset()
    {    
        ini_set("display_errors", "0");
        error_reporting(0);
  
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate()) {
            $email  =   $_POST['PasswordResetRequestForm']['email'];           
            //print_r($_POST);
            
            if ($this->sendEmail($email)) {
                Yii::$app->getSession()->setFlash('success', 'Check your email for further instructions.');
            } else {
                Yii::$app->getSession()->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('/administrator/user/requestPasswordResetToken', [
            'model' => $model,
        ]);
    }
    
     /**
     * Reset password
     * @return string
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->getSession()->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('/administrator/user/resetPassword', [
                'model' => $model,
        ]);
    }
    
    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */
    public function sendEmail($email)
    {
        ini_set("display_errors", "0");
        error_reporting(0);
 
        try {
            /* @var $user User */
            $user = User::findOne([
                'status' => User::STATUS_ACTIVE,
                'email' => $email,
            ]);
            if (!$user) {
                return false;
            }        
            if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
                $user->generatePasswordResetToken();
                if (!$user->save()) {
                    return false;
                }
            }
            $body_msg = $this->renderPartial('../../../common/mail/passwordResetToken-html', ['user' => $user,]);

            $from = "support@cnaap.net";
            $from_name = "SCCM Team";
            $subject = "Password Reset - SCCM";
            $to =   $email;
            $destination_path = '';
            $cc  = [["email" => "jitendra.gamit@infinitylabs.in", "name" => "Jitendra Gamit"],];

            $result = \common\components\CommonUtility::sendmailWithAttachment($to, $to_name = "", $from, $from_name, $subject, $body_msg, 
                                       $destination_path, $cc);

            if ($result) {
                return true;
            } else {
                return false;
            }
        } catch(\Exception $e) {
            ApiLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }
}
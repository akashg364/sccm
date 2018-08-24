<?php

namespace backend\controllers;

use Yii;
use mdm\admin\models\form\Login;
use backend\models\PasswordResetRequestForm;
use backend\models\ResetPasswordForm;
use backend\models\AuthAssignment;
use backend\models\AuthAssignmentSearch;
use backend\models\Signup;
use backend\models\ChangePassword;
use mdm\admin\models\User;
use backend\models\User as UserSearch;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\base\UserException;
use yii\mail\BaseMailer;
use backend\models\Provider;
use backend\models\ProviderSearch;
use backend\models\Customer;
use backend\models\CustomerSearch;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
/**
 * User controller
 */
class UserController extends Controller
{
    private $_oldMailPath;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'logout' => ['post'],
                    'activate' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if (Yii::$app->has('mailer') && ($mailer = Yii::$app->getMailer()) instanceof BaseMailer) {
                /* @var $mailer BaseMailer */
                $this->_oldMailPath = $mailer->getViewPath();
                $mailer->setViewPath('@mdm/admin/mail');
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        if ($this->_oldMailPath !== null) {
            Yii::$app->getMailer()->setViewPath($this->_oldMailPath);
        }
        return parent::afterAction($action, $result);
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('/administrator/user/index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $renderMethod =(Yii::$app->request->isAjax) ?"renderAjax":"render";
        return $this->$renderMethod('/administrator/user/view', [
                'model' => $this->findModel($id),
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        try{

        $this->findModel($id)->delete();

        return $this->redirect(['index']);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Login
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->getUser()->isGuest) {
            return $this->goHome();
        }

        $model = new Login();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                    'model' => $model,
            ]);
        }
    }

    /**
     * Logout
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->getUser()->logout();

        return $this->goHome();
    }

    /**
     * Signup new user
     * @return string
     */
    public function actionSignup()
    {

        try{
           
        $model = new Signup();
        if ($model->load(Yii::$app->getRequest()->post())) {
            if ($user = $model->signup()) {
                return $this->goHome();
            }
        }

        return $this->render('signup', [
                'model' => $model,
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Request reset password
     * @return string
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequest();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->getSession()->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->getSession()->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
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
            $model = new ResetPassword($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->getSession()->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
                'model' => $model,
        ]);
    }

    /**
     * Reset password
     * @return string
     */
    public function actionChangePassword()
    {
        $model = new ChangePassword();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->change()) {
            return $this->goHome();
        }

        return $this->render('change-password', [
                'model' => $model,
        ]);
    }

    /**
     * Activate new user
     * @param integer $id
     * @return type
     * @throws UserException
     * @throws NotFoundHttpException
     */
    public function actionActivate($id)
    {
        /* @var $user User */
        $user = $this->findModel($id);
        if ($user->status == User::STATUS_INACTIVE) {
            $user->status = User::STATUS_ACTIVE;
            if ($user->save()) {
                //return $this->goHome();
                 return $this->redirect(['index']);
            } else {
                $errors = $user->firstErrors;
                throw new UserException(reset($errors));
            }
        }
        return $this->goHome();
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    /**
    * Create new user
    * @return string
    */
    public function actionCreate()
    {
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();    
        $model = new Signup();
        if ($model->load(Yii::$app->getRequest()->post())) {           
                if(strstr(strtolower($user->user_type), 'super')){
                    if(strstr($model->user_type, 'super')){                        
                        $model->reference_id    =0;                
                    }elseif(strstr($model->user_type, 'provider')){                        
                        $model->reference_id    =   $model->provider;
                    }elseif(strstr($model->user_type, 'customer')){
                        $model->reference_id    =   $model->customer;                       
                    }
            }           
            //$model->user_type   =strtolower($model->user_type);
            if ($user = $model->signup()) {
//                $authAssignment =   new AuthAssignment();
//                $authAssignment->item_name  = ucfirst($model->user_type);
//                $authAssignment->user_id    =   $user->id;
//                $authAssignment->save();
                return $this->redirect(['index']);                                                          
            }
        }
//        $ddRoles = ArrayHelper::map(AuthAssignmentSearch::find()->select('item_name')->distinct()->where(['!=','item_name','SuperAdmin' ])->all(), 'item_name','item_name');
//        $ddRoles    =  array_change_key_case($ddRoles,CASE_LOWER);
        $ddProviderItems    =   ArrayHelper::map(ProviderSearch::find(['active_status' => 1])->all(), 'id', 'company_name');  
        $renderMethod =(Yii::$app->request->isAjax) ?"renderAjax":"render";
        return $this->$renderMethod('/administrator/user/create', [
                'model' => $model,
                'provider'  =>$ddProviderItems,
                'user'  =>  $user,
//                'roles' =>  $ddRoles
        ]);
    }
    
    /**
     * function for ajax for on select of usertype dropdown 
     */
    public function actionLists($id)
    {
        $options    =   "";
        switch ($id) {
        case (strstr($id, 'provider')):           
            $ddProviderItems    =   ArrayHelper::map(Provider::find(['active_status' => 1])->all(), 'id', 'company_name');            
            $options    =   "<option value=''>Select Provider</option>";
            foreach($ddProviderItems as $key    =>  $val){
                $options.=  '<option value='.$key.'>'.$val.'</option>';                
            }
            echo $options;
            break;
        case (strstr($id, 'customer')):
            $options    =   "<option value=''>Select Customer</option>";
            $ddCustomerItems    =   ArrayHelper::map(Customer::find(['active_status' => 1])->all(), 'id', 'company_name');
            foreach($ddCustomerItems as $key    =>  $val){
                $options.=  '<option value='.$key.'>'.$val.'</option>';              
            }
             echo $options;
            break;    
        default:
            echo $id;
        }
    }     
     /**
     * function for ajax for on select of usertype dropdown 
     */
    public function actionCustomers($id)
    {
        $options    =   "";        
        $options    =   "<option value=''>Select Customer</option>";
        $ddCustomerItems = Customer::find()
                     ->where(['active_status' => 1,'provider_id'=>$id])->asArray()
                     ->all();
//        print_r($ddCustomerItems);
        foreach($ddCustomerItems as $key    =>  $val){
            $options.=  '<option value='.$val['id'].'>'.$val['company_name'].'</option>';              
        }
        echo $options;
          
    }    
    
}

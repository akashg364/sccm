<?php

namespace backend\controllers;

use Yii;
use backend\models\ResourceManager;
use backend\models\VariablesMaster;
use backend\models\DeviceRole;
use backend\models\Device;
use backend\models\ResourceManagerSearch;
use backend\models\ServiceModelTemplate;
use backend\models\ServiceModelTemplateVariables;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use mdm\admin\models\User;
use backend\models\RtRange;

/**
 * ResourceManagerController implements the CRUD actions for ResourceManager model.
 */
class ResourceManagerController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all ResourceManager models.
     * @return mixed
     */
    public function actionIndex() {
        try{

        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        $searchModel = new ResourceManagerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'user' => $user,
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Displays a single ResourceManager model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        
        try{

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('view', [
                        'model' => $this->findModel($id),
            ]);
        }

        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    private function setParameterValue($model) {
        $value_type = $model->value_type;
        if ($value_type == "range") {
            $model->variable_value = $model->parameter_min_value . "," . $model->parameter_max_value;
        } else {
            $model->variable_value = $model->parameter_min_value;
        }
    }

    /**
     * Creates a new ResourceManager model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        try{

        $model = new ResourceManager();

        if ($model->load(Yii::$app->request->post())) {
            $this->setParameterValue($model);
            if ($model->save()) {
                Yii::$app->session->setFlash("success", "Resource manager added successfully");
                return $this->redirect(['index']);
            }
        }
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                        'model' => $model,
            ]);
        }
        return $this->render('create', [
                    'model' => $model,
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Updates an existing ResourceManager model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        try{

        $model = $this->findModel($id);
        if ($model) {
            $model->parameter_min_value = $model->variable_value;
            if ($model->value_type == 'range') {
                $valueArr = explode(",", $model->variable_value);
                $model->parameter_min_value = $valueArr[0];
                $model->parameter_max_value = $valueArr[1];
            }
        }
        $customer_name = [];
        if ($model->customer) {
            $customer_name = [$model->customer_id => $model->customer->company_name];
        }
        if ($model->load(Yii::$app->request->post())) {
            $this->setParameterValue($model);
            if ($model->save()) {
                Yii::$app->session->setFlash("success", "Resource manager updated successfully");
                return $this->redirect(['index']);
            }
        }

        $renderMethod = (Yii::$app->request->isAjax) ? "renderAjax" : "render";
        return $this->$renderMethod('update', [
                    'model' => $model,
                    'customer_name' => $customer_name,
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Deletes an existing ResourceManager model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        try{

        $this->findModel($id)->delete();

        return $this->redirect(['index']);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Finds the ResourceManager model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ResourceManager the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = ResourceManager::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * 
     * @param int $id
     * @param int $status
     * @return index
     * this function is update active_status field for accept/reject
     * in the time of insert 0->default,1->accepted,2->rejected
     */
    public function actionUpdatestatus($id, $status) {
       try{

        $model = new ResourceManager();
        $model = $this->findModel($id);
        $model->acceptanceActionTaken_by = Yii::$app->user->id;
        $model->approve_status = $status;
        $model->save(false);
        return $this->redirect(['index']);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * 
     * @param int $id
     * @param int $status
     * @return index
     * this function is update active_status field for active/inactive
     * in the time of insert 0->inactive,1->active
     */
    public function actionUpdateactiveinactive($id, $status) {
        try{

        $model = new ResourceManager();
        $model = $this->findModel($id);
        $model->active_status = $status;
        $model->statusActionTaken_by = Yii::$app->user->id;
        $model->save(false);
        return $this->redirect(['index']);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    public function actionServiceVariables($role = 1, $device, $id = null) {
        $systemVar = '';
        $userVar = '';
        $serviceTemplate = ServiceModelTemplate::find()
                ->where(['service_model_id' => $id, 'device_role_id' => $role])
                ->One();
        $serviceVariables = ServiceModelTemplateVariables::find()
                ->where(['template_id' => $serviceTemplate['id']])
                ->All();

        if (count($serviceVariables) > 0) {
            foreach ($serviceVariables as $value) {
                $resourceVariables = VariablesMaster::find()
                        ->where(['id' => $value->resource_manager_id])
                        ->One();

                if ($resourceVariables['type'] == 'user') {
                    $userVar .= '<div class="form-group field-serviceinstance-' . $resourceVariables['variable_name'] . ' required">';
                    $userVar .= '<label class="control-label" for="serviceinstance-' . $resourceVariables['variable_name'] . '">User Defined : ' . ucfirst($resourceVariables['variable_name']) . '</label>';
                    $userVar .= '<input id="serviceinstance-' . $resourceVariables['variable_name'] . $device . '" class="form-control" name="ServiceInstance[devices][' . $device . '][UserDefined][' . $resourceVariables['variable_name'] . ']" aria-required="true" type="text">';
                    $userVar .= '<div class="help-block"></div></div>';
                }

//                if($resourceVariables['type'] == 'system')
//                {
//                    $systemVar .= '<div class="form-group field-serviceinstance-' . $resourceVariables['variable_name'] . ' required">';
//                    $systemVar .= '<label class="control-label" for="serviceinstance-' . $resourceVariables['variable_name'] . '">System Defined : ' . ucfirst($resourceVariables['variable_name']) . '</label>';
//                    $systemVar .= '<input id="serviceinstance-' . $resourceVariables['variable_name'] . '" class="form-control" name="ServiceInstance[devices][' . $device . '][SystemDefined][' . $resourceVariables['variable_name'] . ']" aria-required="true" readonly="true" type="text" value="' . ResourceManager::getSystemDefinedValue($serviceTemplate['id'], 1, $resourceVariables['id']) . '">';
//                    $systemVar .= '<div class="help-block"></div></div>';
//                }
            }
        }

//        $finalString = $userVar . $systemVar . '<div class="form-group field-serviceinstance-nso_payload">';
//        $finalString .= '<label class="control-label" for="serviceinstance-nso_payload">Nso Payload</label>';
//        $finalString .= '<textarea id="serviceinstance-nso_payload" class="form-control" name="ServiceInstance[devices][' . $device . '][nso_payload]" readonly="" rows="6">' . $serviceTemplate['nso_payload'] . '</textarea>';
//        $finalString .= '<div class="help-block"></div>';
//        $finalString .= '</div>';
        $finalString = $userVar . '<input id="serviceinstance-template-' . $device . '" class="form-control" name="ServiceInstance[devices][' . $device . '][template]" aria-required="true" type="hidden" value="' . $serviceTemplate['id'] . '">';

        return $finalString;
    }

    public function actionEndpointsTabs($model, $endpoints) {
        $deviceTabs = '<div id="endpoint_error" class="alert-danger"></div>
		<ul class="nav nav-tabs">';
        for ($i = 0; $i < $endpoints; $i++) {
            $deviceTab = "Device" . $i;
            $activeTab = ($i == 0) ? "active" : "";
            $deviceTabs .= '<li class="' . $activeTab . '">';
            $deviceTabs .= '<a data-toggle="tab" href="#device' . $i . '">Device' . $i . '</a>';
            $deviceTabs .= '</li>';
        }
        $deviceTabs .= '</ul>';

        $deviceTabs .= '<div class="tab-content">';
        for ($j = 0; $j < $endpoints; $j++) {
            $activeTab = ($j == 0) ? "fade in active" : "";
            $deviceTabs .= '<div id="device' . $j . '" class="tab-pane ' . $activeTab . '">';
            $deviceTabs .= '<h3></h3>';
            $deviceTabs .= '<div>';
            $deviceTabs .= '<div class="form-group field-serviceinstance-device_id' . $j . ' required">';
            $deviceTabs .= '<label class="control-label" for="serviceinstance-device_id' . $j . '">Device</label>';
            $deviceTabs .= '<select id="serviceinstance-device_id' . $j . '" class="form-control" name="ServiceInstance[devices][' . $j . '][device]" aria-required="true" aria-invalid="true">';
            $deviceTabs .= '<option value=""></option>';
            $deviceList = Device::getDeviceList();
            foreach ($deviceList as $key => $value) {
                $deviceTabs .= '<option value="' . $key . '">' . $value . '</option>';
            }

            $deviceTabs .= '</select>';
            $deviceTabs .= '<div class="help-block"></div>';
            $deviceTabs .= '</div>';
            $deviceTabs .= '<div class="form-group field-serviceinstance-role_id' . $j . ' required">';
            $deviceTabs .= '<label class="control-label" for="serviceinstance-role_id' . $j . '">Role</label>';
            $deviceTabs .= '<select id="role-' . $j . '" class="form-control js-device-role" name="ServiceInstance[devices][' . $j . '][role]"  aria-required="true" aria-invalid="true">';
            $deviceTabs .= '<option value=""></option>';
            $deviceRoleList = DeviceRole::getDeviceRolesList();
            foreach ($deviceRoleList as $key => $value) {
                $deviceTabs .= '<option value="' . $key . '">' . $value . '</option>';
            }
            $deviceTabs .= '</select>';
            $deviceTabs .= '<div class="help-block"></div>';
            $deviceTabs .= '</div>';
            $deviceTabs .= '<div id="service-device-data-' . $j . '" class="nso_payload"></div>';
            $deviceTabs .= '</div>';
            $deviceTabs .= '</div>';
        }
        $deviceTabs .= '</div>';
        return $deviceTabs;
    }

    public function actionTest() {
        echo "<pre>";
        $data = ResourceManager::getSystemDefinedVariablesArray(2, 1, 1, 1, 19);
        $insertData = ResourceManager::insertDeviceVariableMapping(1, 1, $data['key']);
        print_r($insertData);
        print_r($data);
    }

    public function actionEndpointsTabsLab($model, $endpoints) {
        $deviceTabs = '<div id="endpoint_error" class="alert-danger"></div>
		<ul class="nav nav-tabs">';
        for ($i = 0; $i < $endpoints; $i++) {

            if ($i == 0) {
                $activeTab = ($i == 0) ? "active" : "";
                $deviceTabs .= '<li class="' . $activeTab . '">';
                $deviceTabs .= '<a data-toggle="tab" href="#device' . $i . '">Device</a>';
                $deviceTabs .= '</li>';
            }
        }
        $deviceTabs .= '</ul>'; 
        $deviceTabs .= '<div class="tab-content">';
        for ($j = 0; $j < $endpoints; $j++) {
            $activeTab = ($j == 0) ? "fade in active" : "";
            $deviceTabs .= '<div id="device' . $j . '" class="tab-pane ' . $activeTab . '">';
            $deviceTabs .= '<h3></h3>';
            $deviceTabs .= '<div>';
            $deviceTabs .= '<div class="form-group field-serviceinstance-device_id' . $j . ' required">';
            $deviceTabs .= '<label class="control-label" for="serviceinstance-device_id' . $j . '">Device</label>';
            $deviceTabs .= '<select id="serviceinstance-device_id' . $j . '" class="form-control" name="ServiceInstance[devices][' . $j . '][device]" aria-required="true" aria-invalid="true">';
            $deviceTabs .= '<option value=""></option>';
            $deviceList = Device::getDeviceList();

            foreach ($deviceList as $key => $value) {
                $deviceTabs .= '<option value="' . $key . '">' . $value . '</option>';
            }

            $deviceTabs .= '</select>';
        }
        $deviceTabs .= '<div class="help-block"></div>';
        $deviceTabs .= '</div>';
        $deviceTabs .= '<div class="form-group field-serviceinstance-role_id required">';
        $deviceTabs .= '<label class="control-label" for="serviceinstance-role_id">Role</label>';
        $deviceTabs .= '<select id="role" class="form-control js-device-role" name="ServiceInstance[devices][role]"  aria-required="true" aria-invalid="true">';
        $deviceTabs .= '<option value=""></option>';
        $deviceRoleList = DeviceRole::getDeviceRolesList();

        foreach ($deviceRoleList as $key => $value) {
            $deviceTabs .= '<option value="' . $key . '">' . $value . '</option>';
        }
        $deviceTabs .= '</select>';
        $deviceTabs .= '<div class="help-block"></div>';
        $deviceTabs .= '</div>';
        $deviceTabs .= '<div id="service-device-data" class="nso_payload"></div>';
        $deviceTabs .= '</div>';
        $deviceTabs .= '</div>';

        $deviceTabs .= '</div>';
        return $deviceTabs;
    }

    public function actionServiceVariablesLab($role = 1, $device, $id = null) {
        $systemVar = '';
        $userVar = '';
        $serviceTemplate = ServiceModelTemplate::find()
                ->where(['service_model_id' => $id, 'device_role_id' => $role])
                ->One();
        $serviceVariables = ServiceModelTemplateVariables::find()
                ->where(['template_id' => $serviceTemplate['id']])
                ->All();

        if (count($serviceVariables) > 0) {
            foreach ($serviceVariables as $value) {
                $resourceVariables = VariablesMaster::find()
                        ->where(['id' => $value->resource_manager_id])
                        ->One();

                if ($resourceVariables['type'] == 'user') {
                    $userVar .= '<div class="form-group field-serviceinstance-' . $resourceVariables['variable_name'] . ' required">';
                    $userVar .= '<label class="control-label" for="serviceinstance-' . $resourceVariables['variable_name'] . '">User Defined : ' . ucfirst($resourceVariables['variable_name']) . '</label>';
                    $userVar .= '<input id="serviceinstance-' . $resourceVariables['variable_name'] . $device . '" class="form-control" name="ServiceInstance[UserDefined][' . $resourceVariables['variable_name'] . ']" aria-required="true" type="text">';
                    $userVar .= '<div class="help-block"></div></div>';
                }

//                if($resourceVariables['type'] == 'system')
//                {
//                    $systemVar .= '<div class="form-group field-serviceinstance-' . $resourceVariables['variable_name'] . ' required">';
//                    $systemVar .= '<label class="control-label" for="serviceinstance-' . $resourceVariables['variable_name'] . '">System Defined : ' . ucfirst($resourceVariables['variable_name']) . '</label>';
//                    $systemVar .= '<input id="serviceinstance-' . $resourceVariables['variable_name'] . '" class="form-control" name="ServiceInstance[devices][' . $device . '][SystemDefined][' . $resourceVariables['variable_name'] . ']" aria-required="true" readonly="true" type="text" value="' . ResourceManager::getSystemDefinedValue($serviceTemplate['id'], 1, $resourceVariables['id']) . '">';
//                    $systemVar .= '<div class="help-block"></div></div>';
//                }
            }
        }

//        $finalString = $userVar . $systemVar . '<div class="form-group field-serviceinstance-nso_payload">';
//        $finalString .= '<label class="control-label" for="serviceinstance-nso_payload">Nso Payload</label>';
//        $finalString .= '<textarea id="serviceinstance-nso_payload" class="form-control" name="ServiceInstance[devices][' . $device . '][nso_payload]" readonly="" rows="6">' . $serviceTemplate['nso_payload'] . '</textarea>';
//        $finalString .= '<div class="help-block"></div>';
//        $finalString .= '</div>';
        $finalString = $userVar . '<input id="serviceinstance-template" class="form-control" name="ServiceInstance[template]" aria-required="true" type="hidden" value="' . $serviceTemplate['id'] . '">';

        return $finalString;
    }
}

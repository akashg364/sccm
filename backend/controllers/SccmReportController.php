<?php

namespace backend\controllers;

use Yii;
use backend\models\SccmReport;

use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;



/**
 * SccmReportController implements the CRUD actions for SccmReport model.
 */
class SccmReportController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
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
     * Lists all SccmReport models.
     * @return mixed
     */
    public function actionIndex()
    {
        try{

        $SccmReport = new SccmReport();
        $dataProvider = new ActiveDataProvider([
            'query' => SccmReport::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Displays a single SccmReport model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        try{

        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Creates a new SccmReport model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        try{

        $model = new SccmReport();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->renderAjax('create', [
            'model' => $model,
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    public function actionConfigurations(){
        $id = Yii::$app->request->get('id');
        $model = SccmReport::findOne($id);
        
        
 
        if (isset($_FILES) && !empty($_FILES)) {
            
            $fileName_allinone = '';
            $fileType_allinone = '';
            $fileTempName_allinone = '';
            $fileSize_allinone = '';
            $fileName_ag1 = '';
            $fileType_ag1 = '';
            $fileTempName_ag1 = '';
            $fileSize_ag1 = '';
            $fileName_ag2 = '';
            $fileType_ag2 = '';
            $fileTempName_ag2 = '';
            $fileSize_ag2 = '';
            $fileName_css = '';
            $fileType_css = '';
            $fileTempName_css = '';
            $fileSize_css = '';
            define ('SITE_ROOT', \Yii::getAlias('@webroot'));
//            echo \Yii::getAlias('@webroot');
            $targetFilePath = SITE_ROOT.'/uploads/';
           
            chown($targetFilePath, 0777);
            $i = 0;
             foreach($_FILES['fileupload'] as $fileup)
             {
                 
                 switch($i)
                 {
                     case 0:
                     $fileName_allinone = $fileup['all_in_one'];
                     $fileName_css = $fileup['css'];
                     $fileName_ag1 = $fileup['AG1'];
                     $fileName_ag2 = $fileup['AG2'];
                     break;
                     case 1:
                     $fileType_allinone = $fileup['all_in_one'];
                     $fileType_css = $fileup['css'];
                     $fileType_ag1 = $fileup['AG1'];
                     $fileType_ag2 = $fileup['AG2'];
                     break;
                     case 2:
                     $fileTempName_allinone = $fileup['all_in_one'];
                     $fileTempName_css = $fileup['css'];
                     $fileTempName_ag1 = $fileup['AG1'];
                     $fileTempName_ag2 = $fileup['AG2'];
                     break;
                     case 3:
                     $fileSize_allinone = $fileup['all_in_one'];
                     $fileSize_css = $fileup['css'];
                     $fileSize_ag1 = $fileup['AG1'];
                     $fileSize_ag2 = $fileup['AG2'];
                     break;
                 }
                 $i++;
             }
             
             $allInOneFileUploaded = '';
             $cssFileUploaded = '';
             $ag1FileUploaded = '';
             $ag2FileUploaded = '';
             if (!file_exists($targetFilePath.'/'.$id.'/configurations/')) {
                mkdir($targetFilePath.'/'.$id.'/configurations/', 0777, true);
            }
             $allInOneFileUploaded = (move_uploaded_file($fileTempName_allinone, $targetFilePath.'/'.$id.'/configurations/'.$fileName_allinone))?$fileName_allinone:'';
             $cssFileUploaded = (move_uploaded_file($fileTempName_css, $targetFilePath.'/'.$id.'/configurations/'.$fileName_css))?$fileName_css:'';
             $ag1FileUploaded = (move_uploaded_file($fileTempName_ag1, $targetFilePath.'/'.$id.'/configurations/'.$fileName_ag1))?$fileName_ag1:'';
             $ag2FileUploaded = (move_uploaded_file($fileTempName_ag2, $targetFilePath.'/'.$id.'/configurations/'.$fileName_ag2))?$fileName_ag2:'';
             if($allInOneFileUploaded != "" OR $cssFileUploaded != "" OR $ag1FileUploaded != "" OR $ag2FileUploaded != "")
             {
                 if($allInOneFileUploaded != "")
                 {
                     Yii::$app->db->createCommand()->update('sccm_report', ['all_in_one_file' => $allInOneFileUploaded], 'id='.$id)
                    ->execute();
                 }
                 if($cssFileUploaded != "")
                 {
                     Yii::$app->db->createCommand()->update('sccm_report', ['css_file' => $cssFileUploaded], 'id='.$id)
                    ->execute();
                 }
                 if($ag1FileUploaded != "")
                 {
                     Yii::$app->db->createCommand()->update('sccm_report', ['ag1_file' => $ag1FileUploaded], 'id='.$id)
                    ->execute();
                 }
                 if($ag2FileUploaded != "")
                 {
                     Yii::$app->db->createCommand()->update('sccm_report', ['ag2_file' => $ag2FileUploaded], 'id='.$id)
                    ->execute();
                 }
                 
                
                     Yii::$app->db->createCommand()->update('sccm_report', ['conf_uploaded' => 2], 'id='.$id)
                    ->execute();
                
                 
                
             }
             return $this->redirect(['sccm-report/index']); 
             exit;
        }

        return $this->renderAjax('configuration_uploads', ['model' => $model]);
    }
    public function actionPayload()
    {
        $id = Yii::$app->request->get('id');
        $model = SccmReport::findOne($id);
        
       
        
        if (isset($_FILES) && !empty($_FILES)) {
            $fileName = $_FILES['fileupload_payload']['name'];
            $fileType = $_FILES['fileupload_payload']['type'];
            $fileTempName = $_FILES['fileupload_payload']['tmp_name'];
            $fileSize = $_FILES['fileupload_payload']['size'];;
           
            define ('SITE_ROOT', \Yii::getAlias('@webroot'));
//            echo \Yii::getAlias('@webroot');
            $targetFilePath = SITE_ROOT.'/uploads/';
           
            chown($targetFilePath, 0777);
            
            if (!file_exists($targetFilePath.'/'.$id.'/payload/')) {
                mkdir($targetFilePath.'/'.$id.'/payload/', 0777, true);
            }
            $FileUploaded = (move_uploaded_file($fileTempName, $targetFilePath.'/'.$id.'/payload/'.$fileName))?$fileName:'';
            
            if($FileUploaded != "")
                 {
                     Yii::$app->db->createCommand()->update('sccm_report', ['payload' => $FileUploaded], 'id='.$id)
                    ->execute();
                 }
             return $this->redirect(['sccm-report/index']); 
             exit;
        }
        return $this->renderAjax('payload_upload', ['model' => $model]);
    }
    
     public function actionDryrun()
    {
         $id = Yii::$app->request->get('id');
        $model = SccmReport::findOne($id);
        
       
        
        if (isset($_FILES) && !empty($_FILES)) {
            $fileName = $_FILES['fileupload_dryrun']['name'];
            $fileType = $_FILES['fileupload_dryrun']['type'];
            $fileTempName = $_FILES['fileupload_dryrun']['tmp_name'];
            $fileSize = $_FILES['fileupload_dryrun']['size'];
           
            define ('SITE_ROOT', \Yii::getAlias('@webroot'));
//            echo \Yii::getAlias('@webroot');
            $targetFilePath = SITE_ROOT.'/uploads/';
           
            chown($targetFilePath, 0777);
            
            if (!file_exists($targetFilePath.'/'.$id.'/dryrun/')) {
                mkdir($targetFilePath.'/'.$id.'/dryrun/', 0777, true);
            }
            $FileUploaded = (move_uploaded_file($fileTempName, $targetFilePath.'/'.$id.'/dryrun/'.$fileName))?$fileName:'';
            
            if($FileUploaded != "")
            {
                Yii::$app->db->createCommand()->update('sccm_report', ['dryrun' => $FileUploaded], 'id='.$id)
               ->execute();
            }
            return $this->redirect(['sccm-report/index']); 
             exit;
            
        }
        return $this->renderAjax('dryrun_upload', ['model' => $model]);
    }
    
     public function actionL2document()
    {
         $id = Yii::$app->request->get('id');
        $model = SccmReport::findOne($id);
        
       
        
        if (isset($_FILES) && !empty($_FILES)) {
            $fileName = $_FILES['fileupload_l2document']['name'];
            $fileType = $_FILES['fileupload_l2document']['type'];
            $fileTempName = $_FILES['fileupload_l2document']['tmp_name'];
            $fileSize = $_FILES['fileupload_l2document']['size'];
           
            define ('SITE_ROOT', \Yii::getAlias('@webroot'));
//            echo \Yii::getAlias('@webroot');
            $targetFilePath = SITE_ROOT.'/uploads/';
           
            chown($targetFilePath, 0777);
            
            if (!file_exists($targetFilePath.'/'.$id.'/l2document/')) {
                mkdir($targetFilePath.'/'.$id.'/l2document/', 0777, true);
            }
            $FileUploaded = (move_uploaded_file($fileTempName, $targetFilePath.'/'.$id.'/l2document/'.$fileName))?$fileName:'';
            
            if($FileUploaded != "")
            {
                Yii::$app->db->createCommand()->update('sccm_report', ['l2_document' => $FileUploaded], 'id='.$id)
               ->execute();
            }
            return $this->redirect(['sccm-report/index']); 
             exit;
            
        }
        return $this->renderAjax('l2document_upload', ['model' => $model]);
    }
    
    public function actionDownload(){
        $id = Yii::$app->request->get('id');
        $name = Yii::$app->request->get('name');
        $fileFolder = Yii::$app->request->get('type');
        $url = Yii::$app->request->baseUrl.'/uploads/'.$id.'/'.$fileFolder.'/'.$name;
        
     
        ignore_user_abort(true);
        set_time_limit(0); // disable the time limit for this script

        $path = $url; // change the path to fit your websites document structure


        $fullPath = $_SERVER['DOCUMENT_ROOT'].$path;
 
        if ($fd = fopen ($fullPath, "r")) {
            $fsize = filesize($fullPath);
            $path_parts = pathinfo($fullPath);
            $ext = strtolower($path_parts["extension"]);
            switch ($ext) {
                case "pdf":
                header("Content-type: application/pdf");
                header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); // use 'attachment' to force a file download
                break;
                // add more headers for other content types here
                default;
                header("Content-type: application/octet-stream");
                header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
                break;
            }
            header("Content-length: $fsize");
            header("Cache-control: private"); //use this to open files directly
            while(!feof($fd)) {
                $buffer = fread($fd, 2048);
                echo $buffer;
            }
        }
        fclose ($fd);
        exit;

    }
    /**
     * Updates an existing SccmReport model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {

        try{

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->renderAjax('update', [
            'model' => $model,
        ]);

        } catch (ErrorException $e) {
            AuditLog::saveLog($e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('/site/error', ['message' => $e->getMessage(), 'name' => 'Oops! Something went wrong!!']);
        }
    }

    /**
     * Deletes an existing SccmReport model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
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
     * Finds the SccmReport model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SccmReport the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SccmReport::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

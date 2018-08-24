<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use dosamigos\fileupload\FileUpload;
use kartik\widgets\FileInput;


/* @var $this yii\web\View */
/* @var $model app\models\SubServices */
/* @var $form yii\widgets\ActiveForm */

?>
 
<div class="sub-services-form">
        <?php $form = ActiveForm::begin(['id' => 'sub-services-form', 'options' => ['enctype' => 'multipart/form-data']]); ?>
        <div class="popup-wheat-bg">
            <input type="hidden" name="ref_id" value="<?=$model->id?>"/>
            <div class="form-group">
            <label class="col-md-2 control-label" for="sccmreport-service_type">ALL IN ONE</label>
            <input type="file" name="fileupload[all_in_one]" />
            </div>
            <div class="form-group">
            <label class="col-md-2 control-label" for="sccmreport-service_type">CSS</label>
            <input type="file" name="fileupload[css]" />
            </div>
            <div class="form-group">
            <label class="col-md-2 control-label" for="sccmreport-service_type">AG1</label>
            <input type="file" name="fileupload[AG1]" />
            </div>
            <div class="form-group">
            <label class="col-md-2 control-label" for="sccmreport-service_type">AG2</label>
            <input type="file" name="fileupload[AG2]" />
            </div>
        </div>
        <div class="modal-footer ">
            <input type="submit" value="Upload" />
        </div>
        <?php ActiveForm::end(); ?>

</div>

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
            <label class="col-md-2 control-label" for="sccmreport-service_type">Dryrun</label>
            <input type="file" name="fileupload_dryrun" />
            </div>
            
        </div>
        <div class="modal-footer ">
            <input type="submit" value="Upload" />
        </div>
        <?php ActiveForm::end(); ?>

</div>

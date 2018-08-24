<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\Services;
use backend\models\SubServices;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\models\Topology */
/* @var $form yii\widgets\ActiveForm */
?>
<?php
//services
$smodel = new Services();
$data_test = $smodel->getServicesAll();
$data_list = array();

// foreach ($data_test as  $value) {
//   $data_list[$value->id]=$value->name;
// }
//subservices    
// $ssmodel = new SubServices();
$data_test_s = array(); //$ssmodel->getSubservicesAll();
?>

<div class="">

<?php $form = ActiveForm::begin(["id"=>"topology-form"]); ?>
    <div class="popup-wheat-bg">

        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'description')->textarea(['rows' => '6']) ?>
        <?php echo Yii::$app->yiiHelper->getActiveStatusField($form,$model);?>
    </div>
    <div class="modal-footer ">
        <?= Html::submitButton('Save', ['class' => 'btn btn-swim']) ?>
        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-swim', "data-dismiss" => "modal"]) ?>
    </div>
    <?php ActiveForm::end();   
    $script = <<< JS
    $(function(){
        $('#topology-sid').trigger("change");
    });
    </script>
JS;
$this->registerJs($script);?>
</div>


  
<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$subnetArr = ['64' => '64', '65' => '65', '66' => '66', '67' => '67', '68' => '68', '69' => '69', 
              '70' => '70', '71' => '71', '72' => '72', '73' => '73', '74' => '74', '75' => '75', '76' => '76', '77' => '77', '78' => '78', '79' => '79', 
              '80' => '80', '81' => '81', '82' => '82', '83' => '83', '84' => '84', '85' => '85' , '86' => '86', '87' => '87', '88' => '88', '89' => '89', 
              '90' => '90', '91' => '91', '92' => '92', '93' => '93', '94' => '94', '95' => '95', '96' => '96', '97' => '97', '98' => '98', '99' => '99', 
             '100' => '100', '101' => '101', '102' => '102', '103' => '103', '104' => '104', '105' => '105', '106' => '106', '107' => '107', '108' => '108', '109' => '109', 
             '110' => '110', '111' => '111', '112' => '112', '113' => '113', '114' => '114', '115' => '115', '116' => '116', '117' => '117', '118' => '118', '119' => '119', 
             '120' => '120', '121' => '121', '122' => '122', '123' => '123', '124' => '124', '125' => '125', '126' => '126', '127' => '127', '128' => '128'];

/* @var $this yii\web\View */
/* @var $model backend\models\Ipv6Pool */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ipv6-pool-form">
    <?php $form = ActiveForm::begin(['id'=>'ipv4-pool-form', 'enableAjaxValidation' => true]); ?>
    <div class="popup-wheat-bg">
        <?= $form->field($model, 'pool')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'subnet')->dropDownList($subnetArr) ?>
        <?= $form->field($model, 'is_active')->dropDownList(['1' => 'Active', '0' => 'In Active']) ?>
    </div>
    <div class="modal-footer">
        <?= Html::submitButton('Save', ['class' => 'btn btn-swim']) ?>
        <?= Html::a('Cancel', ['index'], ['class'=>'btn btn-swim ',"data-dismiss"=>"modal"]) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
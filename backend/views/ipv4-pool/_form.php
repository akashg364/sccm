<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Ipv4Pool */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ipv4-pool-form">
    <?php $form = ActiveForm::begin(['id'=>'ipv4-pool-form']); ?>
    <div class="popup-wheat-bg">
        <?= $form->field($model, 'pool')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'is_active')->dropDownList(['1' => 'Active', '0' => 'In Active']) ?>
    </div>
    <div class="modal-footer">
        <?= Html::submitButton('Save', ['class' => 'btn btn-swim']) ?>
        <?= Html::a('Cancel', ['index'], ['class'=>'btn btn-swim ',"data-dismiss"=>"modal"]) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
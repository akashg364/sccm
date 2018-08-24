<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\Customer;
use backend\models\ResourceManager;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $model app\models\GenericVariables */
/* @var $form yii\widgets\ActiveForm */


$customers = new Customer();
$customer_list = $customers->getCustomers();

?>

<div class="generic-variables-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'customer_id')->dropDownList(ArrayHelper::map($customer_list, 'id', 'company_name'), ['prompt' => ''])->label('Customer'); ?>

    <?= $form->field($model, 'variable_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'variable_value')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

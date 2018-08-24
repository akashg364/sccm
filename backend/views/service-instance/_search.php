<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\ServiceInstanceSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="service-instance-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>
    
    <?= $form->field($model, 'service_order_id') ?>

    <?= $form->field($model, 'customer_id') ?>

    <?= $form->field($model, 'service_model_id') ?>

    <?php // echo $form->field($model, 'scheduled_status') ?>

    <?php // echo $form->field($model, 'scheduled_date') ?>

    <?php // echo $form->field($model, 'is_active') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

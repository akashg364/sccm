<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Ipv6PoolAssignmentSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ipv6-pool-assignment-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'pool_id') ?>

    <?= $form->field($model, 'service_instance_id') ?>

    <?= $form->field($model, 'subnet') ?>

    <?= $form->field($model, 'usable_ips') ?>

    <?php // echo $form->field($model, 'start_ip') ?>

    <?php // echo $form->field($model, 'end_ip') ?>

    <?php // echo $form->field($model, 'ip_count') ?>

    <?php // echo $form->field($model, 'device_id') ?>

    <?php // echo $form->field($model, 'is_full') ?>

    <?php // echo $form->field($model, 'created_date') ?>

    <?php // echo $form->field($model, 'updated_date') ?>

    <?php // echo $form->field($model, 'is_active') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

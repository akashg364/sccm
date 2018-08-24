<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\ServiceInstanceDevices;

/* @var $this yii\web\View */
/* @var $model backend\models\ServiceInstanceDevices */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Service Instance Devices', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-instance-devices-view">
    <div class="popup-wheat-bg">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php //Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php /*/* Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ])*/ ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'template_id',
            [
                'label'=>'Service',
                'format' => 'raw',
                'value' => function ($model) {
                    if($model->template_id > 0)
                        return ServiceInstanceDevices::getServiceModelByTemplate($model->template_id);
                }
            ],
            'serviceInstance.service_order_id',
            'device.hostname',
            'deviceRole.role_name',
			[
					"attribute"=>"user_defined_data",
					"format"=>"raw",
					"value"=> jsonPretty(unserialize($model->user_defined_data)),
				],
			[
					"attribute"=>"system_defined_data",
					"format"=>"raw",
					"value"=> jsonPretty(unserialize($model->system_defined_data)),
				],	
				[
					"attribute"=>"nso_payload",
					"format"=>"raw",
					"value"=> jsonPretty($model->nso_payload),
				],	
            'created_by',
            'created_date',
            'updated_by',
            'updated_date',
            [
                'attribute' => 'is_active',
                'label'=>'Status',
                'format' => 'raw',
                'value' => function ($data) {
                    if($data->is_active == 1)
                        return "Active";
                    else if($data->is_active == 0)
                        return "InActive";
                }
            ],
        ],
    ]) ?>
    </div>
</div>

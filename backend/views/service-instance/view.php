<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\ServiceInstance */

$this->title = $model->service_order_id;
$this->params['breadcrumbs'][] = ['label' => 'Service Instances', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-instance-view">
    <div class="popup-wheat-bg">
        <h1><?= Html::encode($this->title) ?></h1>

        <p>
            <?= Html::a('Reload', ['view', 'id' => $model->id], ['class' => 'btn btn-primary showModalButton', 'modalType' => 'modal-full']) ?>
            <?php /* /*Html::a('Delete', ['delete', 'id' => $model->id], [
              'class' => 'btn btn-danger',
              'data' => [
              'confirm' => 'Are you sure you want to delete this item?',
              'method' => 'post',
              ],
              ]) */ ?>
        </p>

        <?=
        DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'service_order_id',
                'customer.company_name',
                'serviceModel.name',
                //'user_defined_data:ntext',
                //'system_defined_data:ntext',
                // 'nso_payload:ntext',
                'scheduled_status',
                'uniqueId',
                [
                    'label' => 'Deployment Status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        if ($model->id > 0) {
                            return '<span style="color:' . $model->serviceStatusColorArr[$model->status] . '">' . $model->serviceStatusArr[$model->status] . '</span>';
                        }
                    }
                ],
                [
                    'label' => 'Action',
                    'format' => 'raw',
                    'value' => function ($model) {
                        if ($model->id > 0) {
                            return $model->serviceActionArr[$model->action];
                        }
                    }
                ],
                'scheduled_date',
                [
                    'attribute' => 'is_active',
                    'label' => 'Status',
                    'format' => 'raw',
                    'value' => function ($data) {
                        if ($data->is_active == 1)
                            return "Active";
                        else if ($data->is_active == 0)
                            return "InActive";
                    }
                ],
                [
                    "attribute" => "user_defined_data",
                    "format" => "raw",
                    "value" => function($data) {
                        $udHtml = jsonPretty($data->user_defined_data);
                        // $udArray = json_decode($data->user_defined_data);
                        // $udHtml = "<table>";
                        // foreach($udArray as $var=>$value){
                        // }
                        // $udHtml = "</table>";
                        return $udHtml;
                    }
                ],
                [
                    "attribute" => "final_nso_payload",
                    "format" => "raw",
                    "value" => jsonPretty($model->final_nso_payload),
                ],
                [
                    "attribute" => "remarks",
                    "format" => "raw",
                    "value" => jsonPretty($model->remarks),
                ]
            ],
        ])
        ?>
    </div>
</div>
<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Provider */

$this->title = $model->company_name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Providers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="provider-view">

    <h1><?= Html::encode($this->title) ?></h1>

<!--    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>-->

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'company_name',
            'description:ntext',
            'email_id',
            'mobile_number',
            'address',
            'city',
            'state',
            'country',
            [                    
                    'label'=>   'Active Status',                    
                    'value' => function ($data) {
                        if($data->active_status == 1)
                            return "Active";
                        else if($data->active_status == 0)
                            return "InActive";
                    }
                ],
//              [                   
//                    'label'=>   'Approve Status',                    
//                    'value' => function ($data) {
//                        if($data->approve_status == 1)
//                            return "Approved";
//                        else if($data->approve_status == 0)
//                            return "Not Approved";
//                    }
//                ],           
//            '',
            'created_date',
            'updated_date',
            ],
    ]) ?>

</div>

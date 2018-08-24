<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\SccmReport */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Sccm Reports', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sccm-report-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            array(
                'name'=>'service_type',
                'label'=>'Service Type',
                'type'=>'raw',
                'value'=>$model->getServiceName($model->service_type)
             ),
            array(
                'name'=>'sub_service_type',
                'label'=>'Sub Service Type',
                'type'=>'raw',
                'value'=>$model->SubServices($model->sub_service_type)
             ),
            array(
                'name'=>'managed_unmanaged',
                'label'=>'Managed/Unmanaged',
                'type'=>'raw',
                'value' => function ($model) {
                    $mngdunmngd = $model->managed_unmanaged;
                    return $mngdunmngd = ($mngdunmngd=='1')?'Managed':'Unmanaged';
                }
             ),
            array(
                'name'=>'termination_point',
                'label'=>'CPE Termination Point',
                'type'=>'raw',
                'value' => function ($model) {
                    $termination_point = $model->termination_point;
                    return $termination_point = ($termination_point=='1')?'CSS':'';
                }
             ),
           
            array(
                'name'=>'spur_dual_homed',
                'label'=>'Spur/ Dual Homed',
                'type'=>'raw',
                'value' => function ($model) {
                    $dual_homed = $model->spur_dual_homed;
                    return $dual_homed = ($dual_homed=='1')?'Spur':'Dual Homed';
                }
             ),
            array(
                'name'=>'with_mds',
                'label'=>'With Mds',
                'type'=>'raw',
                'value' => function ($model) {
                    $with_mds = $model->with_mds;
                    return $with_mds = ($with_mds=='1')?'No':'Yes';
                }
             ),
            array(
                'name'=>'with_eds',
                'label'=>'With Eds',
                'type'=>'raw',
                'value' => function ($model) {
                    $with_eds = $model->with_eds;
                    return $with_eds = ($with_eds=='1')?'No':'Yes';
                }
             ),
            array(
                'name'=>'tagged_type',
                'label'=>'Tagged Type',
                'type'=>'raw',
                'value' => function ($model) {
                    $tagged_type = $model->tagged_type;
                    return $tagged_type = ($tagged_type=='1')?'Tagged':'Untagged';
                }
             ),
            array(
                'name'=>'single_dual_pe',
                'label'=>'PE Type Single/Dual PE',
                'type'=>'raw',
                'value' => function ($model) {
                    $tagged_type = $model->single_dual_pe;
                    return $tagged_type = ($tagged_type=='1')?'Tagged':'Untagged';
                }
             ),
             array(
                'name'=>'single_dual_pe',
                 'label'=>'Routing Protocol',
                'type'=>'raw',
                'value' => function ($model) {
                $routing_protocol = $model->routing_protocol;
                return $routing_protocol = ($routing_protocol=='1')?'BGP':'Untagged';
                }
             ),
            array(
                'name'=>'concat_data',
                'label'=>'Concatenated Data',
                'type'=>'raw',
                'value' => function ($model) {
                $concatenated = '';
                
                $id =$model->sub_service_type;
                $subservice =  $model->SubServices($id);
                $concatenated.= $subservice.'-';
                $mngdunmngd = $model->managed_unmanaged;
                $mngdunmngd = ($mngdunmngd=='1')?'Managed':'Unmanaged';
                $concatenated.= $mngdunmngd.'-';
                $termination_point = $model->termination_point;
                $termination_point = ($termination_point=='1')?'CSS':'';
                $concatenated.= $termination_point.'-';
                $dual_homed = $model->spur_dual_homed;
                $dual_homed = ($dual_homed=='1')?'Spur':'Dual Homed';
                $concatenated.= $dual_homed.'-';
                $with_eds = $model->single_dual_pe;
                $with_eds = ($with_eds=='1')?'Tagged':'Untagged';
                $concatenated.= $with_eds.'-';
                    return $concatenated;
                }
             ),
            array(
                'name'=>'conf_uploaded',
                 'label'=>'Conf Uploaded',
                'type'=>'raw',
                'value' => function ($model) {
                $conf_uploaded = $model->conf_uploaded;
                return $routing_protocol = ($conf_uploaded=='1')?'Not Uploaded':'Uploaded';
                }
             ),
            'created_by',
            
            'created_on',
            'payload',
            'dryrun',
            'l2_document',
        ],
    ]) ?>

</div>

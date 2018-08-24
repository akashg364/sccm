<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\SubServices */
$model->setCreatedByUpdatedByUser();
use backend\models\SubServices;

$this->title = $model->ref_id." - ".$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Sub Services', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sub-services-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php /* echo Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']);
        echo Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) */ ?>
    </p>
     <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //'id',
            'name',
            'description',
			'is_managed',
			'routing_protocol',
			'terminated_at',
			'topology_type',
			'home_type',
			'eds',
            'created_by',
            'created_date',
            'updated_by',
            'updated_date',
            [
               'attribute' =>'active_status',
                'value' => getYesNo($model->active_status)
            ],
            [
               'attribute' =>'components',
               'format'=>'raw',
                'value' => function($model){//SubServices::getComponentList($model->id)
                    $data = SubServices::getComponentList($model->id);
                    $html = "<ol>";
                    if(!empty($data)){
                        foreach ($data as $component => $blocks) {
                            $blocksHtml = "<ul>";
                            foreach($blocks as $block){
                                $blocksHtml .= "<li>$block</li>";
                            }
                             $blocksHtml .= "</ul>";
                            $html .= "<li><b>{$component}</b>{$blocksHtml}</li>";
                        }
                    }    
                    $html .="</ol>";
                    return $html;
                }
            ]

        ],
    ]) ?>

</div>

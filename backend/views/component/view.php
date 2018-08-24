<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\ComponentBlocks;
use backend\models\Component;


/* @var $this yii\web\View */
/* @var $model backend\models\Component */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Components', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="component-view">

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
	 'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => ''],

        'attributes' => [
            'id',
            'name',
            'system_name',
            'description:ntext',
            'blocks:ntext',
            'created_by',
            'created_date',
            'updated_by',
            'updated_date',
            [
                'label'=> 'Component Blocks',
                'format'=>'raw',
                'value'=>function($model){
                     $html = "<ol>";
                     if(!empty($model->componentBlocks)){
                        foreach ($model->componentBlocks as $componentBlock) {
                            $html .="<li>".$componentBlock->block_name."</li>";
                        }
                    } 
                    $html .= "</ol>";
                    return $html;
                }
            ]
        ],
    ]) ?> 
</div>

<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\VariablesMapping */

$this->title = 'View Variable Mapping: '.$model->variableMaster->variable_name;
$this->params['breadcrumbs'][] = ['label' => 'Variables Mappings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="variables-mapping-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php // Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary showModalButton']) ?>
        <?php /* Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ])*/ ?>
    </p>

    <?=  DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'variableMaster.variable_name',
            'customer.company_name',
        ],
    ]);

    $varMaster = $model->variableMaster;
    $label1 = !empty($varMaster->value1_label)?$varMaster->value1_label:"Value 1";
    $label2 = !empty($varMaster->value2_label)?$varMaster->value2_label:"Value 2";
    ?>
    <table class="table table-striped table-bordered">
        <tr>
            <th><?php echo $label1;?></th>
            <th><?php echo $label2;?></th>
            <th>Status</th>
        </tr>
        <?php 

       
        if(isset($model->variableValues)){
            $values = $model->variableValues;
              
                foreach ($values as $key => $valueModel) {
                    $completed = "<span class='label label-success'>Active</span>";
                    $rowClass =  "success";
                    if($valueModel->is_completed){
                        $completed = "<span class='label label-danger'>Completed</span>";
                        $rowClass =  "danger";
                    }
                    echo  "<tr class='{$rowClass}'>
                            <td>".$valueModel->value1."</td>
                            <td>".$valueModel->value2."</td>
                            <td>".$completed."</td>
                         </tr>";
                    
                }
        }

    ?>
    </table>
   

</div>

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  <?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Role */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="role-form">
    <?php $form = ActiveForm::begin(); ?>   
    <?= $form->field($model, 'role')->textInput(['maxlength' => true]) ?>   
    <?php 
        $role =   $model->role;
        $privilegesArr  =   explode('|',$model->privileges);
        $selected    =   array();
        foreach($privilegesArr as $k=>$v){
            $selected[$v]   =   array('Selected'=>true);    
        }
        $options['options']    = $selected;
        $options['multiple']    =   'multiple';
        echo $form->field($modelPrivilege, 'name')->dropDownList($dataProvider, $options)->label('Privileges');       
    ?>
    <?php        
       echo $form->field($model, 'active_status')->radioList(  [
         '1'=>'Active',
         '0'=>'Inactive'
    ])->label('Active Status');
    ?>
    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>




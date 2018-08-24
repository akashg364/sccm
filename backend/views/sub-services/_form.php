<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\SubServices */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sub-services-form">
        <?php $form = ActiveForm::begin(['id' => 'sub-services-form']); ?>
    <div class="popup-wheat-bg">
        <?= $form->field($model, 'ref_id')->textInput(['maxlength' => true]) ?>
        
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'description')->textarea(['rows' => '6']) ?>

        <?php
        foreach ($sub_service_filters as $key => $value) {

            $label_key = array('is_managed' => 'Is Managed',
                'terminated_at' => 'Terminated At',
                'topology_type' => 'Topology Type',
                'routing_protocol' => 'Routing Protocol',
                'dual' => 'Dual',
                'EDS' => 'EDS'
            );

            $value = array_map('ucwords', $value);

            if (in_array($key, ['terminated_at', 'routing_protocol'])) {
                $value = array_change_key_case(array_map('strtoupper', $value), CASE_UPPER);
            }
            if ($key == 'dual') {
                $type_name = 'home_type';
            } else if ($key == 'EDS') {
                $type_name = 'eds';
            } else {
                $type_name = $key;
            }
            ?>
            <?php
            echo $form->field($model, $type_name)->radioList($value)->label($label_key[$key]);
            ?>

        <?php } ?>

        <?php echo Yii::$app->yiiHelper->getActiveStatusField($form, $model); ?>
        <label class="control-label">Components</label><br/>
        <?php
        foreach ($components as $component) {
            echo "<div style='font-weight:bold;'>".$component["name"]."</div>";
            echo "<br/>";
            if (isset($assign_components[$component["id"]])) {
                $model->components[$component["id"]] = $assign_components[$component["id"]];
            }
            $componentBlockList = ArrayHelper::map($component["componentBlocks"], "block_name", "block_name");
            echo $form->field($model, 'components[' . $component["id"] . '][]')->widget(Select2::classname(), [
                'model' => $model,
                'data' => $componentBlockList,
                'options' => ['placeholder' => "Select Component Sub Blocks", 'multiple' => true],
            ])->label(false);
        }
// OLD CODE as Commented on 10-08-2018     
//    $assign_elements_keys  = [];
//        if(!empty($assign_components)) {
//                $assign_elements =  ArrayHelper::map($assign_components, "id", "name");
//                $assign_elements_keys = array_keys($assign_elements);
//        }
//
//        echo Html::checkboxList('SubServices[component]',$assign_elements_keys, ArrayHelper::map($components, "id", "name"),array('class' => 'test' ));
        ?>
    </div>
    <div class="modal-footer ">
<?= Html::submitButton('Save', ['class' => 'btn btn-swim']) ?>
        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-swim', "data-dismiss" => "modal"]) ?>
    </div>
        <?php ActiveForm::end(); ?>

</div>

<?php
yii\bootstrap\Modal::begin([
    //'headerOptions' => ['id' => 'modalHeader'],
	//'headerOptions'=>['style'=>'display:none;'],
    'options' => [
      //  'id' => 'kartik-modal',
        'tabindex' => false // important for Select2 to work properly
    ],
    'header'=>'<h4 class="modal-title" id="modalHeader"></h4>',
    'id' => 'modal',
    //'size' => 'modal-lg',
    //keeps from closing modal with esc key or by clicking out of the modal.
    // user must click cancel or X to close
    'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE]
]);
echo "<div class='batch-info'></div>
     <div id='modalContent'></div>";
yii\bootstrap\Modal::end();
?>

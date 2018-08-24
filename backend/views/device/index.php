<?php
use yii\helpers\Html;
use yii\grid\GridView;
use common\models\User;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\DeviceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Devices';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="device-index">

    <h1><?php //echo Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p><?php  
    $user = User::find()->where(['id' => Yii::$app->user->id])->one();
    //if (trim($user['user_type']) == 'superuser' || trim($user['user_type']) == 'superadmin') { ?>
        <?php //echo Html::a('Create Device', ['create'], ['class' => 'btn-add showModalButton']) 
		echo Html::button('Create Device', ['value' => Url::to(['device/create']), 'class' => 'btn-add showModalButton'])."&nbsp;&nbsp;";
		//echo Html::button('SyncFrom for All device', ['value' => Url::to(['device/sync-from-all']), 'class' => 'btn-add showModalButton', 'id'=>'SyncFromSelect']);
		echo Html::button('SyncFrom for All device', ['id' => 'SyncFromSelect', 'class' => 'btn-add', 'disabled' => 'disabled'])."&nbsp;&nbsp;";
	   // echo Html::button('SyncTo for All device', ['id' => 'SyncToSelect', 'class' => 'btn-add', 'disabled' => 'disabled'])."&nbsp;&nbsp;";
	  ?>
		<?php //} ?>
		
		<?php 
		// / echo Html::button('SyncFrom for All device', ['id' => 'SyncFromSelect', 'value' => 'test', 'class' => 'btn-add showModalButton', 'disabled' => 'disabled']);
		?>
		
		<?php 
		// / echo Html::button('SyncTo for All device', ['id' => 'SyncToSelect', 'value' => 'test', 'class' => 'btn-add showModalButton', 'disabled' => 'disabled']);
		?>
		
    </p>
	
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\CheckboxColumn'],

          //  'id',
            //'hostname',
            [
            	"attribute"=>"hostname",
            	"format"=>"raw",
            	"value"=>function($data){
            		return Html::a($data->hostname,Url::to(["device/view","id"=>$data->id]),["class"=>"showModalButton"]);
            	}
            ],
            'location',
			//'sapid',
            //'loopback',
            //'created_date',
            //'modified_date',
            //'created_by',
            //'modified_by',

           ['class' => 'yii\grid\ActionColumn',
            'buttons' => [
                
				/*
				'dryrun' => function ($url, $model, $key) {
					return Html::button('Dryrun', ['id' => 'dryrun', 'value' => $model->id, 'class' => 'btn dryrun']);
                },
				***/
			
				'SyncFrom' => function ($url, $model, $key) {
				return Html::button('SyncFrom', [ 'value' => Url::to(["device/sync-from","id"=>$model->id]), 'class' => 'btn-add showModalButton']);
				//return Html::button('SyncFrom', ['id' => 'SyncFrom', 'value' => $model->id, 'class' => 'btn-add showModalButton']);
                },
			    
				// 'SyncTo' => function ($url, $model, $key) {
				// return Html::button('SyncTo', [ 'value' => Url::to(["device/sync-to","id"=>$model->id]), 'class' => 'btn-add showModalButton']);
			    // },
				
				'SyncCheck' => function ($url, $model, $key) {
				return Html::button('SyncCheck', [ 'value' => Url::to(["device/sync-check","id"=>$model->id]), 'class' => 'btn-add showModalButton']);
			    },
				
				
				/*
				'SyncCheck' => function ($url, $model, $key) {
				return Html::button('SyncCheck', [ 'value' => Url::to(["device/sync-check","id"=>$model->id]), 'class' => 'btn-add showModalButton']);
			    },
				*/
				
				
				
				'Delete' => function ($url, $model, $key) {
				return Html::button('Delete', [ 'value' => Url::to(["device/delete","id"=>$model->id]), 'class' => 'btn-add showModalButton']);
			    },
				
				
				
				/*
				'delete' => function ($url, $model, $key) {
                    return Html::button('Check Sync', ['id' => 'deleteservice', 'value' => $model->id, 'class' => 'btn-add showModalButton']);
			    },
				*/
				
				
				
				
				
				/*
				'commit' => function ($url, $model, $key) {
                 	return Html::button('Commit', ['id' => 'commit', 'value' => $model->id, 'class' => 'btn commit', 'disabled' => 'disabled']);
				},
				
				'delete' => function ($url, $model, $key) {
                    return Html::button('Delete', ['id' => 'deleteservice', 'value' => $model->id, 'class' => 'btn deleteservice']);
			    },
				***/
		    ],
          'template' => '{SyncFrom} &nbsp;  {SyncCheck} &nbsp; {Delete}'
        ],
			
        ],
    ]); ?>
</div>


<?php 
$urlsyncfrom = Url::to(["device/sync-from"]);   
$urlsyncfromall = Url::to(["device/sync-from-all"]);
$urlsyncto = Url::to(["device/sync-to"]);   
$urlsynctoall = Url::to(["device/sync-to-all"]);
$urlcreatedevice = Url::to(["device/create"]); 
?>


<?php
    Modal::begin([
            'header' => '<h4>Result</h4>',
            'id'     => 'sync-from-modal',
            'size'   => 'model-lg',
    ]);
    
    echo "<div id='modelContent'></div>";
    Modal::end();
 
?>

<?php 

echo $this->registerJs(
	"$('#SyncFromSelect').click(function(){
	   $('#modalContent').html('');
	   var device_id = [];
        $(':checkbox:checked').each(function(i){
          device_id[i] = $(this).val();
        });
	   $('#modalHeader').html('Sync From for All device');
	   $('#modal').find('.modal-content').addClass('seim-popup');
	   $('#modalContent').html(faLoader);
	   $.ajax({
			url:'$urlsyncfromall',
			type:'POST',
			data: {device_id:device_id},
			success:function(data){
				$('#modalContent').html(data);
			}
		});
		$('#modal').modal('show');
		//$('#sync-from-modal').modal('show');
	})");
 
 
 echo $this->registerJs(
	"$('#SyncToSelect').click(function(){
	    var device_id = [];
        $(':checkbox:checked').each(function(i){
          device_id[i] = $(this).val();
        });
	   
	   $.ajax({
			url:'$urlsynctoall',
			type:'POST',
			data: {device_id:device_id},
			success:function(data){
				$('#modelContent').html(data);
			}
		});
	$('#sync-from-modal').modal('show')
	})");
 
 echo $this->registerJs(
	"
	// If any Checkbox clicked
	$(':checkbox').click(function(){
		var device_id = [];
		$(':checkbox:checked').each(function(i){
          device_id[i] = $(this).val();
        });
		if(device_id.length>0){
			$('#SyncFromSelect,#SyncToSelect').prop('disabled', false);
		}else{
			$('#SyncFromSelect,#SyncToSelect').prop('disabled', true);
		}
	});
	$('.select-on-check-all').click(function(){
	  if ($('.select-on-check-all').is(':checked')) {
		   $('#SyncFromSelect').prop('disabled', false);
		   $('#SyncToSelect').prop('disabled', false);
		   
		   //$('#SyncFromSelect').addClass('btn-add');
		   //$('#SyncToSelect').addClass('btn-add ');
	  } else {
		    $('#SyncFromSelect').prop('disabled', true);
			$('#SyncToSelect').prop('disabled', true);
			
			//$('#SyncFromSelect').removeClass('btn-add');
			//$('#SyncToSelect').removeClass('btn-add');
	  }
	})");
	
?>



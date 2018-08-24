<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\SubServices */

$this->title = 'Update: '.$model->ref_id." - ".$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Sub Services', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="sub-services-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'components' => $components,
		'sub_service_filters' => $sub_service_filters,
	'assign_components' => $assign_components
    ]) ?>

</div>

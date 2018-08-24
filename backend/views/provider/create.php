<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Provider */

$this->title = Yii::t('app', 'Create Provider');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Providers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
//        'dataProvider'  =>  $dataProvider,
//        'roleSearchModel'   =>  $roleSearchModel
    ]) ?>

</div>

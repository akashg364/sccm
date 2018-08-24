<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\NetworkEngineers */

$this->title = 'Create Network Engineers';
$this->params['breadcrumbs'][] = ['label' => 'Network Engineers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="network-engineers-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

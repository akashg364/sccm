<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Ipv6PoolAssignment */

$this->title = 'Create Ipv6 Pool Assignment';
$this->params['breadcrumbs'][] = ['label' => 'Ipv6 Pool Assignments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ipv6-pool-assignment-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

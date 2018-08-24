<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Ipv4Pool */

$this->title = 'Create Ipv4 Pool';
$this->params['breadcrumbs'][] = ['label' => 'Ipv4 Pools', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ipv4-pool-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Ipv6Pool */

$this->title = 'Create Ipv6 Pool';
$this->params['breadcrumbs'][] = ['label' => 'Ipv6 Pools', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ipv6-pool-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

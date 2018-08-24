<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Ipv6Subnetting */

$this->title = 'Create Ipv6 Subnetting';
$this->params['breadcrumbs'][] = ['label' => 'Ipv6 Subnettings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ipv6-subnetting-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

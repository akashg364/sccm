<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\ResourceManager */

$this->title = 'Create Resource Manager';
$this->params['breadcrumbs'][] = ['label' => 'Resource Managers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="resource-manager-create">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

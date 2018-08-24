<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Webhooks */

$this->title = 'Create Webhooks';
$this->params['breadcrumbs'][] = ['label' => 'Webhooks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="webhooks-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

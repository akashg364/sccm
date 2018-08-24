<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\RtRange */

$this->title = 'Create Rt Range';
$this->params['breadcrumbs'][] = ['label' => 'Rt Ranges', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="rt-range-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

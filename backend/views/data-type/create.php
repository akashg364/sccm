<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\DataType */

$this->title = 'Create Data Type';
$this->params['breadcrumbs'][] = ['label' => 'Data Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="data-type-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

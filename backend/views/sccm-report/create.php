<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\SccmReport */

$this->title = 'Create Sccm Report';
$this->params['breadcrumbs'][] = ['label' => 'Sccm Reports', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sccm-report-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

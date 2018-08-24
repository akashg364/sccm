<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\GenericVariables */

$this->title = 'Create Generic Variables';
$this->params['breadcrumbs'][] = ['label' => 'Generic Variables', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="generic-variables-create">

    <!--<h1>//?= Html::encode($this->title) ?></h1>-->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

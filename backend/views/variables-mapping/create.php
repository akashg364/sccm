<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\VariablesMapping */

$this->title = 'Create Variables Mapping';
$this->params['breadcrumbs'][] = ['label' => 'Variables Mappings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="variables-mapping-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

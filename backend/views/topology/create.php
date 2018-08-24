<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Topology */

$this->title = 'Create Topology';
$this->params['breadcrumbs'][] = ['label' => 'Topologies', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="topology-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

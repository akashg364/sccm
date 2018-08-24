<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\L2Testing */

$this->title = 'Create L2 Testing';
$this->params['breadcrumbs'][] = ['label' => 'L2 Testings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="l2-testing-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

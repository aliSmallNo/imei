<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\UserModel */

$this->title = 'Update User Model: ' . $model->uId;
$this->params['breadcrumbs'][] = ['label' => 'User Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->uId, 'url' => ['view', 'id' => $model->uId]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="user-model-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

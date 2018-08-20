<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\UserModel */

$this->title = $model->uId;
$this->params['breadcrumbs'][] = ['label' => 'User Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-model-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->uId], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->uId], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'uId',
            'uRole',
            'uCert',
            'uVIP',
            'uName',
            'uPhone',
            'uEmail:email',
            'uOpenId',
            'uUnionId',
            'uUniqid',
            'uMPUId',
            'uInvitedBy',
            'uPassword',
            'uThumb',
            'uAvatar',
            'uMarital',
            'uLocation',
            'uHomeland',
            'uProvince',
            'uCity',
            'uScope',
            'uGender',
            'uBirthYear',
            'uHoros',
            'uHeight',
            'uWeight',
            'uIncome',
            'uEducation',
            'uProfession',
            'uEstate',
            'uCar',
            'uSmoke',
            'uAlcohol',
            'uBelief',
            'uFitness',
            'uDiet',
            'uRest',
            'uPet',
            'uInterest',
            'uIntro',
            'uAlbum',
            'uNote',
            'uStatus',
            'uSubStatus',
            'uHint',
            'uFilter',
            'uCoord',
            'uWorkType',
            'uEmployer',
            'uParent',
            'uSibling',
            'uDwelling',
            'uHighSchool',
            'uUniversity',
            'uMusic',
            'uBook',
            'uMovie',
            'uRawData',
            'uCertImage',
            'uCertStatus',
            'uCertDate',
            'uCertNote',
            'uRank',
            'uRankDate',
            'uRankTmp',
            'uSetting',
            'uApprovedBy',
            'uApprovedOn',
            'uAddedOn',
            'uAddedBy',
            'uUpdatedOn',
            'uUpdatedBy',
            'uLogDate',
        ],
    ]) ?>

</div>

<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'User Models';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-model-index">

	<h1><?= Html::encode($this->title) ?></h1>

	<p>
		<?= Html::a('Create User Model', ['create'], ['class' => 'btn btn-success']) ?>
	</p>
	<?= GridView::widget([
		'dataProvider' => $dataProvider,
		'columns' => [
			['class' => 'yii\grid\SerialColumn'],

			'uId',
			'uRole',
			'uCert',
			'uVIP',
			'uName',
			// 'uPhone',
			// 'uEmail:email',
			// 'uOpenId',
			// 'uUnionId',
			// 'uUniqid',
			// 'uMPUId',
			// 'uInvitedBy',
			// 'uPassword',
			// 'uThumb',
			// 'uAvatar',
			// 'uMarital',
			// 'uLocation',
			// 'uHomeland',
			// 'uProvince',
			// 'uCity',
			// 'uScope',
			// 'uGender',
			// 'uBirthYear',
			// 'uHoros',
			// 'uHeight',
			// 'uWeight',
			// 'uIncome',
			// 'uEducation',
			// 'uProfession',
			// 'uEstate',
			// 'uCar',
			// 'uSmoke',
			// 'uAlcohol',
			// 'uBelief',
			// 'uFitness',
			// 'uDiet',
			// 'uRest',
			// 'uPet',
			// 'uInterest',
			// 'uIntro',
			// 'uAlbum',
			// 'uNote',
			// 'uStatus',
			// 'uSubStatus',
			// 'uHint',
			// 'uFilter',
			// 'uCoord',
			// 'uWorkType',
			// 'uEmployer',
			// 'uParent',
			// 'uSibling',
			// 'uDwelling',
			// 'uHighSchool',
			// 'uUniversity',
			// 'uMusic',
			// 'uBook',
			// 'uMovie',
			// 'uRawData',
			// 'uCertImage',
			// 'uCertStatus',
			// 'uCertDate',
			// 'uCertNote',
			// 'uRank',
			// 'uRankDate',
			// 'uRankTmp',
			// 'uSetting',
			// 'uApprovedBy',
			// 'uApprovedOn',
			// 'uAddedOn',
			// 'uAddedBy',
			// 'uUpdatedOn',
			// 'uUpdatedBy',
			// 'uLogDate',

			['class' => 'yii\grid\ActionColumn'],
		],
	]); ?>
</div>

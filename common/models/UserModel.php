<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "im_user".
 *
 * @property integer $uId
 * @property integer $uRole
 * @property string $uCert
 * @property integer $uVIP
 * @property string $uName
 * @property string $uPhone
 * @property string $uEmail
 * @property string $uOpenId
 * @property string $uUnionId
 * @property string $uUniqid
 * @property integer $uMPUId
 * @property integer $uInvitedBy
 * @property string $uPassword
 * @property string $uThumb
 * @property string $uAvatar
 * @property integer $uMarital
 * @property string $uLocation
 * @property string $uHomeland
 * @property string $uProvince
 * @property string $uCity
 * @property integer $uScope
 * @property integer $uGender
 * @property integer $uBirthYear
 * @property integer $uHoros
 * @property integer $uHeight
 * @property integer $uWeight
 * @property integer $uIncome
 * @property integer $uEducation
 * @property integer $uProfession
 * @property string $uEstate
 * @property integer $uCar
 * @property integer $uSmoke
 * @property integer $uAlcohol
 * @property integer $uBelief
 * @property integer $uFitness
 * @property integer $uDiet
 * @property integer $uRest
 * @property integer $uPet
 * @property string $uInterest
 * @property string $uIntro
 * @property string $uAlbum
 * @property string $uNote
 * @property integer $uStatus
 * @property integer $uSubStatus
 * @property string $uHint
 * @property string $uFilter
 * @property string $uCoord
 * @property string $uWorkType
 * @property string $uEmployer
 * @property string $uParent
 * @property string $uSibling
 * @property string $uDwelling
 * @property string $uHighSchool
 * @property string $uUniversity
 * @property string $uMusic
 * @property string $uBook
 * @property string $uMovie
 * @property string $uRawData
 * @property string $uCertImage
 * @property integer $uCertStatus
 * @property string $uCertDate
 * @property string $uCertNote
 * @property integer $uRank
 * @property string $uRankDate
 * @property integer $uRankTmp
 * @property string $uSetting
 * @property integer $uApprovedBy
 * @property string $uApprovedOn
 * @property string $uAddedOn
 * @property integer $uAddedBy
 * @property string $uUpdatedOn
 * @property integer $uUpdatedBy
 * @property string $uLogDate
 */
class UserModel extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'im_user';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['uRole', 'uVIP', 'uMPUId', 'uInvitedBy', 'uMarital', 'uScope', 'uGender', 'uBirthYear', 'uHoros', 'uHeight', 'uWeight', 'uIncome', 'uEducation', 'uProfession', 'uCar', 'uSmoke', 'uAlcohol', 'uBelief', 'uFitness', 'uDiet', 'uRest', 'uPet', 'uStatus', 'uSubStatus', 'uCertStatus', 'uRank', 'uRankTmp', 'uApprovedBy', 'uAddedBy', 'uUpdatedBy'], 'integer'],
			[['uCertDate', 'uRankDate', 'uApprovedOn', 'uAddedOn', 'uUpdatedOn', 'uLogDate'], 'safe'],
			[['uCert', 'uName', 'uEmail', 'uEstate', 'uCoord', 'uWorkType', 'uEmployer', 'uHighSchool', 'uUniversity'], 'string', 'max' => 128],
			[['uPhone'], 'string', 'max' => 16],
			[['uOpenId', 'uUnionId', 'uUniqid', 'uParent', 'uSibling', 'uDwelling'], 'string', 'max' => 32],
			[['uPassword', 'uProvince', 'uCity'], 'string', 'max' => 64],
			[['uThumb', 'uAvatar', 'uLocation', 'uHomeland', 'uInterest', 'uHint', 'uFilter', 'uMusic', 'uBook', 'uMovie', 'uCertNote'], 'string', 'max' => 256],
			[['uIntro', 'uNote', 'uCertImage', 'uSetting'], 'string', 'max' => 512],
			[['uAlbum'], 'string', 'max' => 4096],
			[['uRawData'], 'string', 'max' => 3072],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'uId' => 'U ID',
			'uRole' => 'U Role',
			'uCert' => 'U Cert',
			'uVIP' => 'U Vip',
			'uName' => 'U Name',
			'uPhone' => 'U Phone',
			'uEmail' => 'U Email',
			'uOpenId' => 'U Open ID',
			'uUnionId' => 'U Union ID',
			'uUniqid' => 'U Uniqid',
			'uMPUId' => 'U Mpuid',
			'uInvitedBy' => 'U Invited By',
			'uPassword' => 'U Password',
			'uThumb' => 'U Thumb',
			'uAvatar' => 'U Avatar',
			'uMarital' => 'U Marital',
			'uLocation' => 'U Location',
			'uHomeland' => 'U Homeland',
			'uProvince' => 'U Province',
			'uCity' => 'U City',
			'uScope' => 'U Scope',
			'uGender' => 'U Gender',
			'uBirthYear' => 'U Birth Year',
			'uHoros' => 'U Horos',
			'uHeight' => 'U Height',
			'uWeight' => 'U Weight',
			'uIncome' => 'U Income',
			'uEducation' => 'U Education',
			'uProfession' => 'U Profession',
			'uEstate' => 'U Estate',
			'uCar' => 'U Car',
			'uSmoke' => 'U Smoke',
			'uAlcohol' => 'U Alcohol',
			'uBelief' => 'U Belief',
			'uFitness' => 'U Fitness',
			'uDiet' => 'U Diet',
			'uRest' => 'U Rest',
			'uPet' => 'U Pet',
			'uInterest' => 'U Interest',
			'uIntro' => 'U Intro',
			'uAlbum' => 'U Album',
			'uNote' => 'U Note',
			'uStatus' => 'U Status',
			'uSubStatus' => 'U Sub Status',
			'uHint' => 'U Hint',
			'uFilter' => 'U Filter',
			'uCoord' => 'U Coord',
			'uWorkType' => 'U Work Type',
			'uEmployer' => 'U Employer',
			'uParent' => 'U Parent',
			'uSibling' => 'U Sibling',
			'uDwelling' => 'U Dwelling',
			'uHighSchool' => 'U High School',
			'uUniversity' => 'U University',
			'uMusic' => 'U Music',
			'uBook' => 'U Book',
			'uMovie' => 'U Movie',
			'uRawData' => 'U Raw Data',
			'uCertImage' => 'U Cert Image',
			'uCertStatus' => 'U Cert Status',
			'uCertDate' => 'U Cert Date',
			'uCertNote' => 'U Cert Note',
			'uRank' => 'U Rank',
			'uRankDate' => 'U Rank Date',
			'uRankTmp' => 'U Rank Tmp',
			'uSetting' => 'U Setting',
			'uApprovedBy' => 'U Approved By',
			'uApprovedOn' => 'U Approved On',
			'uAddedOn' => 'U Added On',
			'uAddedBy' => 'U Added By',
			'uUpdatedOn' => 'U Updated On',
			'uUpdatedBy' => 'U Updated By',
			'uLogDate' => 'U Log Date',
		];
	}
}

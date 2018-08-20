<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\UserModel */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-model-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'uRole')->textInput() ?>

    <?= $form->field($model, 'uCert')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uVIP')->textInput() ?>

    <?= $form->field($model, 'uName')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uPhone')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uEmail')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uOpenId')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uUnionId')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uUniqid')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uMPUId')->textInput() ?>

    <?= $form->field($model, 'uInvitedBy')->textInput() ?>

    <?= $form->field($model, 'uPassword')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uThumb')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uAvatar')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uMarital')->textInput() ?>

    <?= $form->field($model, 'uLocation')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uHomeland')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uProvince')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uCity')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uScope')->textInput() ?>

    <?= $form->field($model, 'uGender')->textInput() ?>

    <?= $form->field($model, 'uBirthYear')->textInput() ?>

    <?= $form->field($model, 'uHoros')->textInput() ?>

    <?= $form->field($model, 'uHeight')->textInput() ?>

    <?= $form->field($model, 'uWeight')->textInput() ?>

    <?= $form->field($model, 'uIncome')->textInput() ?>

    <?= $form->field($model, 'uEducation')->textInput() ?>

    <?= $form->field($model, 'uProfession')->textInput() ?>

    <?= $form->field($model, 'uEstate')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uCar')->textInput() ?>

    <?= $form->field($model, 'uSmoke')->textInput() ?>

    <?= $form->field($model, 'uAlcohol')->textInput() ?>

    <?= $form->field($model, 'uBelief')->textInput() ?>

    <?= $form->field($model, 'uFitness')->textInput() ?>

    <?= $form->field($model, 'uDiet')->textInput() ?>

    <?= $form->field($model, 'uRest')->textInput() ?>

    <?= $form->field($model, 'uPet')->textInput() ?>

    <?= $form->field($model, 'uInterest')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uIntro')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uAlbum')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uNote')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uStatus')->textInput() ?>

    <?= $form->field($model, 'uSubStatus')->textInput() ?>

    <?= $form->field($model, 'uHint')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uFilter')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uCoord')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uWorkType')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uEmployer')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uParent')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uSibling')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uDwelling')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uHighSchool')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uUniversity')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uMusic')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uBook')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uMovie')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uRawData')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uCertImage')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uCertStatus')->textInput() ?>

    <?= $form->field($model, 'uCertDate')->textInput() ?>

    <?= $form->field($model, 'uCertNote')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uRank')->textInput() ?>

    <?= $form->field($model, 'uRankDate')->textInput() ?>

    <?= $form->field($model, 'uRankTmp')->textInput() ?>

    <?= $form->field($model, 'uSetting')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uApprovedBy')->textInput() ?>

    <?= $form->field($model, 'uApprovedOn')->textInput() ?>

    <?= $form->field($model, 'uAddedOn')->textInput() ?>

    <?= $form->field($model, 'uAddedBy')->textInput() ?>

    <?= $form->field($model, 'uUpdatedOn')->textInput() ?>

    <?= $form->field($model, 'uUpdatedBy')->textInput() ?>

    <?= $form->field($model, 'uLogDate')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

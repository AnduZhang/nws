<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\NRESProperty */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="nresproperty-form">

    <?php $form = ActiveForm::begin([
        'id'                     => 'property-form',
//        'enableAjaxValidation'   => false,
//        'enableClientValidation' => false,
//        'validateOnBlur'         => false,
//        'beforeSubmit' => 'alert("2");',

    ]); ?>
    <div class="form-group">
        <?= $form->field($model, 'name')->textInput(['maxlength' => 45]) ?>
    </div>
    <div class="form-group">
    <?= $form->field($model, 'streetAddress')->textInput(['maxlength' => 45]) ?>
    </div>
    <div class="form-group">
    <?= $form->field($model, 'city')->textInput(['maxlength' => 45]) ?>
    </div>
    <div class="form-group">
    <?= $form->field($model, 'state')->textInput(['maxlength' => 45]) ?>
    </div>
    <div class="form-group">
    <?= $form->field($model, 'zipcode')->textInput(['maxlength' => 45]) ?>
    </div>
    <div class="form-group">
    <?= $form->field($model, 'client')->textInput(['maxlength' => 45]) ?>
    </div>
    <div class="form-group">
    <?= $form->field($model, 'latitude')->textInput(['disabled'=>true]) ?>
    </div>
    <div class="form-group">
    <?= $form->field($model, 'longitude')->textInput(['disabled'=>true]) ?>
    </div>
    <div class="form-group">
    <?= $form->field($model, 'status')->dropDownList(\app\models\NRESProperty::getStatusAlias()) ?>
    </div>
    <?= $form->field($model, 'addNew')->hiddenInput() ?>
    <sup class="req">*</sup>  Required fields.
    <div class="form-group">
        <div class="modal-footer">
            <div class="text-center">
                <?php if ($model->isNewRecord)
                    echo Html::submitButton('Save & Add Another', ['class' => 'btn btn-sm btn-gray','id'=>'property-save-add-another']) ?>
                <?= Html::submitButton($model->isNewRecord ? 'Save' : 'Update', ['class' => 'btn btn-sm btn-blue','id'=>'property-save-one']) ?>
                <?= Html::button(Yii::t('user', 'Cancel'), ['class' => 'btn btn-sm btn-link', 'tabindex' => '3','onclick'=>'$(\'#modal\').modal(\'hide\')']) ?>
                <div class="small-ajax-loader"></div>




            </div>
        </div>

    </div>


    <?php ActiveForm::end(); ?>

</div>

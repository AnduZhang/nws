<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\NRESProperty */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="nresproperty-form">
    <div class="alert alert-danger"><strong>Warning:</strong> The property will be permanently deleted.</div>
    <?php $form = ActiveForm::begin([
        'id'                     => 'property-form',


    ]); ?>
    <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
    <div class="form-group">
        <div class="modal-footer">
            <div class="text-center">
                <?= Html::button(Yii::t('user', 'Cancel'), ['class' => 'btn btn-sm btn-link', 'tabindex' => '3','onclick'=>'$(\'#modal\').modal(\'hide\')']) ?>
                <?= Html::submitButton('Delete', ['class' => 'btn btn-sm btn-blue','id'=>'property-delete']) ?>

                <div class="small-ajax-loader"></div>
            </div>
        </div>

    </div>

    <?php ActiveForm::end(); ?>

</div>

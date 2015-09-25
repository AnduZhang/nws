<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id'          => 'account-form',
    'enableAjaxValidation'   => true,
    'enableClientValidation' => false,
]); ?>
    <div class="form-group">
        <?= $form->field($model, 'email') ?>
    </div>
    <div class="form-group">
        <?= $form->field($model, 'username') ?>
    </div>
    <div class="form-group">
        <?= $form->field($model, 'firstName') ?>
    </div>
    <div class="form-group">
        <?= $form->field($model, 'lastName') ?>
    </div>
    <div class="form-group">
        <?= $form->field($model, 'current_password')->passwordInput() ?>
        <p class="fieldNote"><strong>Note: </strong>To change the account settings, please input your current password</p>
    </div>
    <div class="form-group">
        <?= $form->field($model, 'new_password')->passwordInput() ?>
        <p class="fieldNote"><strong>Note: </strong>Fill out this field, if you want to change your current password. Otherwise leave it blank.</p>
    </div>
    <sup class="req">*</sup>  Required fields.

    <div class="modal-footer">
        <div class="text-center">
            <button type="button" class="btn btn-sm btn-link" data-dismiss="modal">CANCEL</button>
            <?= Html::submitButton(Yii::t('user', 'Save'), ['class' => 'btn btn-sm btn-blue']) ?>
        </div>
    </div>
<?php ActiveForm::end(); ?>
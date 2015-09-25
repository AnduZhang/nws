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

/**
 * @var yii\web\View $this
 * @var dektrium\user\models\User $user
 * @var dektrium\user\Module $module
 */


?>

<?php $form = ActiveForm::begin([
    'id'                     => 'registration-form',
    'enableAjaxValidation'   => true,
    'enableClientValidation' => false
]); ?>
<div class="form-group">
    <?= $form->field($model, 'firstName') ?>
</div>
<div class="form-group">
    <?= $form->field($model, 'lastName') ?>
</div>
<div class="form-group">
    <?= $form->field($model, 'username') ?>
</div>
<div class="form-group">
    <?= $form->field($model, 'email') ?>
</div>
<div class="form-group">
    <?= $form->field($model, 'password')->passwordInput() ?>
</div>
<div class="form-group">
    <?= $form->field($model, 'password_repeat')->passwordInput() ?>
</div>
<sup class="req">*</sup>  Required fields.
<div class="modal-footer">
    <div class="text-center">
        <button type="button" class="btn btn-sm btn-link" data-dismiss="modal">CANCEL</button>
        <?= Html::submitButton(Yii::t('user', 'Register'), ['class' => 'btn btn-sm btn-blue']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
<!--<p class="text-center">-->
<!--    --><? //= Html::a(Yii::t('user', 'Already registered? Sign in!'), ['/user/security/login']) ?>
<!--</p>-->

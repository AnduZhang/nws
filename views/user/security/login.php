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
use dektrium\user\widgets\Connect;

/**
 * @var yii\web\View $this
 * @var dektrium\user\models\LoginForm $model
 * @var dektrium\user\Module $module
 */
$this->render('/_alert', ['module' => Yii::$app->getModule('user')]) ?>

<?php $form = ActiveForm::begin([
    'id'                     => 'login-form',
    'enableAjaxValidation'   => true,
    'enableClientValidation' => false,
    'validateOnBlur'         => false,
]) ?>
<div class="form-group">
    <?= $form->field($model, 'login', ['inputOptions' => ['autofocus' => 'autofocus', 'class' => 'form-control', 'tabindex' => '1']]) ?>
</div>

<div class="form-group">
    <?php
    //$form->field($model, 'password', ['inputOptions' => ['class' => 'form-control', 'tabindex' => '2']])->passwordInput()->label(Yii::t('user', 'Password') . ' (' . Html::a(Yii::t('user', 'Forgot password?'), ['/user/recovery/request'], ['tabindex' => '5']) . ')')
     ?>
    <?= $form->field($model, 'password', ['inputOptions' => ['class' => 'form-control', 'tabindex' => '2']])->passwordInput()->label(Yii::t('user', 'Password')) ?>
</div>
<!--<div class="form-group">-->
<?php //echo $form->field($model, 'rememberMe')->checkbox(['tabindex' => '4']) ?>
<!--</div>-->
<div class="form-group">
    <?php if ($module->enableConfirmation): ?>
        <p class="text-center">
            <?= Html::a(Yii::t('user', 'Didn\'t receive confirmation message?'), ['/user/registration/resend']) ?>
        </p>
    <?php endif ?>
    <?= Connect::widget([
        'baseAuthUrl' => ['/user/security/auth']
    ]) ?>
</div>
<sup class="req">*</sup>  Required fields.
<div class="modal-footer">
    <div class="text-center">
        <?= Html::button(Yii::t('user', 'Cancel'), ['class' => 'btn btn-sm btn-link', 'tabindex' => '3','onclick'=>'$(\'#modal\').modal(\'hide\')']) ?>
        <?= Html::submitButton(Yii::t('user', 'Sign in'), ['id'=>'loginBtn','class' => 'btn btn-sm btn-blue', 'tabindex' => '3']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>

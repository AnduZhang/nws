<?php
namespace app\models;
use dektrium\user\helpers\Password;

use dektrium\user\models\LoginForm as BaseLoginForm;


class LoginForm extends BaseLoginForm
{
    public function attributeLabels()
    {
        return [
            'login'      => \Yii::t('user', 'Username'),
            'password'   => \Yii::t('user', 'Password'),
            'rememberMe' => \Yii::t('user', 'Remember me next time'),
        ];
    }

    public function rules()
    {
        return [
            [['login', 'password'], 'required'],
            ['login', 'trim'],
//            ['password', 'match', 'pattern' => '/(?=.*\d)/', 'message' => \Yii::t('user', "Password must include at least one number")],
//            ['password', 'match', 'pattern' => '/(?=.*[A-Z])/', 'message' => \Yii::t('user', "Password must include at least one uppercase letter!")],
//            ['password', 'match', 'pattern' => '/(?=.*[a-z])/', 'message' => \Yii::t('user', "Password must include at least one lowercase letter!")],
//            [['password'], 'string', 'max' => 12,'min'=>8],
            ['password', function ($attribute) {
                if ($this->user === null || !Password::validate($this->password, $this->user->password_hash)) {
                    $this->addError($attribute, \Yii::t('user', 'Invalid username or/and password'));
                }
            }],
            ['login', function ($attribute) {
                if ($this->user !== null) {
                    $confirmationRequired = $this->module->enableConfirmation && !$this->module->enableUnconfirmedLogin;
                    if ($confirmationRequired && !$this->user->getIsConfirmed()) {
                        $this->addError($attribute, \Yii::t('user', 'You need to confirm your email address'));
                    }
                    if ($this->user->getIsBlocked()) {
                        $this->addError($attribute, \Yii::t('user', 'Your account has been blocked'));
                    }
                }
            }],
            ['rememberMe', 'boolean'],
        ];
    }


}
<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace app\models;

use dektrium\user\models\RegistrationForm as BaseRegistrationForm;
use dektrium\user\Module;

/**
 * Registration form collects user input on registration process, validates it and creates new User model.
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class RegistrationForm extends BaseRegistrationForm
{
    /** @var string */
    public $email;

    /** @var string */
    public $username;

    /** @var string */
    public $password;
    public $password_repeat;

    public $firstName;

    public $lastName;

    /** @var User */
    protected $user;


    /** @var Module */
    protected $module;

    /** @inheritdoc */
    public function init()
    {
        $this->user = \Yii::createObject([
            'class'    => User::className(),
            'scenario' => 'register'
        ]);
        $this->module = \Yii::$app->getModule('user');
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            ['firstName', 'required'],
            ['lastName', 'required'],
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'string', 'min' => 8, 'max' => 12],
            ['username', 'match', 'pattern' => '/^[a-zA-Z1-9]\w+$/'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => $this->module->modelMap['User'],
                'message' => \Yii::t('user', 'This username has already been taken')],


            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => $this->module->modelMap['User'],
                'message' => \Yii::t('user', 'This email address has already been taken')],

            [['password','password_repeat'], 'required'],
            ['password', 'string', 'min' => 8, 'max' =>12],

            [['password'], 'match', 'pattern' => '/(?=.*\d)/', 'message' => \Yii::t('user', "Password must include at least one number")],
            [['password'], 'match', 'pattern' => '/(?=.*[A-Z])/', 'message' => \Yii::t('user', "Password must include at least one uppercase letter!")],
            [['password'], 'match', 'pattern' => '/(?=.*[a-z])/', 'message' => \Yii::t('user', "Password must include at least one lowercase letter!")],
            [['password_repeat'], 'compare', 'compareAttribute'=>'password', 'operator'=>'==', 'skipOnEmpty'=>false],
//            ['password_repeat', 'string', 'min' => 8, 'max' =>12],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'email'    => \Yii::t('user', 'Email'),
            'username' => \Yii::t('user', 'Username'),
            'password' => \Yii::t('user', 'Password'),
            'password_repeat' => \Yii::t('user', 'Repeat Password'),
        ];
    }

    /** @inheritdoc */
    public function formName()
    {
        return 'register-form';
    }

    /**
     * Registers a new user account.
     * @return bool
     */
    public function register()
    {
        if (!$this->validate()) {
            return false;
        }

        $this->user->setAttributes([
            'email'    => $this->email,
            'username' => $this->username,
            'password' => $this->password,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ]);

        return $this->user->register();
    }
}
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
use dektrium\user\helpers\Password;

use dektrium\user\models\SettingsForm as BaseSettingsForm;
use dektrium\user\Module;

class SettingsForm extends BaseSettingsForm {

    public $firstName;

    public $lastName;
    /** @inheritdoc */

    public function rules()
    {
        return [
            [['username', 'email', 'current_password','firstName','lastName'], 'required'],
            [['new_password'], 'match', 'pattern' => '/(?=.*\d)/', 'message' => \Yii::t('user', "Password must include at least one number")],
            [['new_password'], 'match', 'pattern' => '/(?=.*[A-Z])/', 'message' => \Yii::t('user', "Password must include at least one uppercase letter!")],
            [['new_password'], 'match', 'pattern' => '/(?=.*[a-z])/', 'message' => \Yii::t('user', "Password must include at least one lowercase letter!")],
            [['new_password'], 'compare', 'compareAttribute'=>'current_password', 'operator'=>'!=', 'skipOnEmpty'=>true,'message'=>'New password must not be equal to the current password'],
            [['username', 'email'], 'filter', 'filter' => 'trim'],
            ['username', 'string', 'min' => 8, 'max' => 12],
            ['username', 'match', 'pattern' => '/^[a-zA-Z]\w+$/'],

            ['email', 'email'],
            [['email', 'username'], 'unique', 'when' => function ($model, $attribute) {
                return $this->user->$attribute != $model->$attribute;
            }, 'targetClass' => $this->module->modelMap['User']],
            ['new_password', 'string', 'min' => 8, 'max' =>12],

            ['current_password', function ($attr) {
                if (!Password::validate($this->$attr, $this->user->password_hash)) {
                    $this->addError($attr, \Yii::t('user', 'Current password is not valid'));
                }
            }]
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'email'            => \Yii::t('user', 'Email'),
            'username'         => \Yii::t('user', 'Username'),
            'new_password'     => \Yii::t('user', 'New password'),
            'current_password' => \Yii::t('user', 'Current password'),
            'firstName'        => \Yii::t('user', 'First Name'),
            'lastName'         => \Yii::t('user', 'Last Name')
        ];
    }

    public function save()
    {
        if ($this->validate()) {
            $this->user->scenario = 'settings';
            $this->user->username = $this->username;
            $this->user->password = $this->new_password;
            $this->user->firstName = $this->firstName;
            $this->user->lastName = $this->lastName;
            if ($this->email == $this->user->email && $this->user->unconfirmed_email != null) {
                $this->user->unconfirmed_email = null;
                \Yii::$app->session->setFlash('info', \Yii::t('user', 'You have successfully cancelled email changing process'));
            } else if ($this->email != $this->user->email) {
                switch ($this->module->emailChangeStrategy) {
                    case Module::STRATEGY_INSECURE:
                        $this->insecureEmailChange(); break;
                    case Module::STRATEGY_DEFAULT:
                        $this->defaultEmailChange(); break;
                    case Module::STRATEGY_SECURE:
                        $this->secureEmailChange(); break;
                    default:
                        throw new \OutOfBoundsException('Invalid email changing strategy');
                }
            }
            return $this->user->save();
        }

        return false;
    }

//    public function save()
//    {
//
//        var_dump($this->validate());die;
//
//
//        return false;
//    }

}
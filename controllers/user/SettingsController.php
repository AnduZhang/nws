<?php

namespace app\controllers\user;


use dektrium\user\controllers\SettingsController as BaseSettingsController;
use dektrium\user\models\Account;
use dektrium\user\models\SettingsForm;

class SettingsController extends BaseSettingsController
{
    public function actionAccount()
    {
        /** @var SettingsForm $model */
        $model = \Yii::createObject(SettingsForm::className());
        $this->performAjaxValidation($model);

        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->session->setFlash('success', \Yii::t('user', 'Account settings have been successfully saved'));
            return $this->goHome();
        }
        $model->firstName = \Yii::$app->user->identity->firstName;
        $model->lastName = \Yii::$app->user->identity->lastName;

        return $this->renderAjax('//user/settings/account', [
            'model' => $model,
        ]);
    }
}

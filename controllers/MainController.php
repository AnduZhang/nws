<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models;


class MainController extends Controller
{
    public function beforeAction($action) {

//        Yii::$app->pa= 'My TST';

        return parent::beforeAction($action);
    }

    protected function performAjaxValidation(Model $model)
    {
        if (\Yii::$app->request->isAjax && $model->load(\Yii::$app->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            echo json_encode(ActiveForm::validate($model));
            \Yii::$app->end();
        }
    }
}

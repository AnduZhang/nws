<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models;
use app\models\lsrFilesStatus;
use yii\helpers\ArrayHelper;
use yii\db\Query;

class AccountController extends MainController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),

                'rules' => [
                    [

                        'allow' => true,
                        'roles' => ['@'],

                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }



    public function actionAlerts()
    {

        $alertsModel = new models\WeatherAlert();

        $searchModelPre = new models\WeatherAlertSearch();
        $searchModelPre->type = 0;
        $dataProviderPre = $searchModelPre->search(ArrayHelper::merge(Yii::$app->request->queryParams,['type'=>0]));

        $searchModelPost = new models\WeatherAlertSearch();
        $searchModelPost->type = 1;
        $dataProviderPost = $searchModelPost->search(ArrayHelper::merge(Yii::$app->request->queryParams,['type'=>1]));

        $unreadedPre = $alertsModel->getUnreadAlertsCount(0);
        $unreadedPost = $alertsModel->getUnreadAlertsCount(1);
//
//        if (Yii::$app->request->isPjax) {
//            return $this->renderAjax('_pre_storm', [
//                'searchModelPre' => $searchModelPre,
//                'dataProviderPre' => $dataProviderPre,
//                'searchModelPost' => $searchModelPost,
//                'dataProviderPost' => $dataProviderPost,
//            ]);
//        }
        return $this->render('alerts', [
            'searchModelPre' => $searchModelPre,
            'dataProviderPre' => $dataProviderPre,
            'searchModelPost' => $searchModelPost,
            'dataProviderPost' => $dataProviderPost,
            'unreadedPre' => $unreadedPre,
            'unreadedPost' => $unreadedPost,
        ]);
    }

    public function actionProperties()
    {

        $model = new lsrFilesStatus();
        $searchModel = new models\lsrFilesStatusSearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);


        return $this->render('properties',['data'=>$dataProvider,'searchModel'=>$searchModel,'model'=>$model]);
    }


}

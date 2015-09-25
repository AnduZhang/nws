<?php

namespace app\controllers;

use app\models\CapForm;
use linslin\yii2\curl\Curl;
use Yii;
use yii\filters\AccessControl;
use app\components;
use yii\filters\VerbFilter;

use app\models;
use kartik\mpdf\Pdf;


class SiteController extends MainController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),

                'rules' => [
                    [

                        'allow' => true,
                        'roles' => ['?'],
                        'actions' => ['index','error','help','pdf']
                    ],
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

    public function beforeAction($action)
    {

        if (parent::beforeAction($action)) {
            if (!\Yii::$app->user->can($action->id)) {
//                throw new ForbiddenHttpException('Access denied');
            }
            return true;
        } else {
            return false;
        }
    }

    public function actionError() {
        return $this->render('error');
    }
    public function actionIndex()
    {

        Yii::error('index');
        $performLogin = false;

        if (isset(Yii::$app->request->get()['login']) && Yii::$app->request->get()['login']) {
            $performLogin = true;
            Yii::$app->session->setFlash('warning','You are not logged in or your session expired.');
        }
        return $this->render('index',['performLogin'=>$performLogin]);
    }

    public function actionMap()
    {
        return $this->render('testMap');
    }

    public function actionLogin()
    {
//        if (!\Yii::$app->user->isGuest) {
//            return $this->goHome();
//        }
//
//        $model = new LoginForm();
//        if ($model->load(Yii::$app->request->post()) && $model->login()) {
//            return $this->goBack();
//        } else {
//            return $this->render('login', [
//                'model' => $model,
//            ]);
//        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    public function actionHelp()
    {
        return $this->render('help');
    }

    public function actionCap()
    {   $model = new CapForm();
        $requestData = null;
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->CAPParser->url = $model->url;
            $requestData['atom'] = Yii::$app->CAPParser->getAtomContent();
            $requestData['cap'] =  Yii::$app->CAPParser->getContent();
        }
        return $this->render('cap',['model'=>$model,'requestData'=>$requestData]);
    }

    public function actionCsv()
    {   $model = new CapForm();
        $requestData = null;
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->CSVParser->url = $model->url;
            $requestData = Yii::$app->CSVParser->getContent();
        }
        return $this->render('csv',['model'=>$model,'requestData'=>$requestData]);
    }

    public function actionGetFileContent()
    {   $model = new CapForm();
        $requestData = null;
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->CSVParser->url = $model->url;
            $requestData = Yii::$app->CSVParser->getContent();
        }
        return $this->render('csv',['model'=>$model,'requestData'=>$requestData]);
    }

    public function actionLsrcontent()
    {
        $model = new models\LsrForm();
        $lsrFileContent = null;

        if ($model->load(Yii::$app->request->post())) {
            $isFileInDb = models\lsrFilesStatus::find()->where(['name'=>$model->name])->one();
            if ($isFileInDb) {
                $lsrFileContent = models\lsrFilesContent::find()->where(['fileId'=>$isFileInDb->id])->orderBy(['time'=>SORT_DESC])->one();
            }
        }
        return $this->render('lsrContent',['model'=>$model,'lsrFileContent'=>$lsrFileContent]);
    }
    public function actionGeo() {

        $model = new models\GeoForm();
        $model->street = '1600 Amphitheatre Pkwy';
        $model->city = 'Mountain View';
        $model->state = 'CA';
        $model->zipcode = '94043';
        $model->country = 'USA';
        $googleGeo = new \stdClass();
        $mapQuest = new \stdClass();
        $bingMap = new \stdClass();
        $returnArray = [];
        $allDataArray = [];
        if ($model->load(Yii::$app->request->post())) {
            $curl = new Curl();

            //Start google operation
            $googleGeo->googleApiKey = 'AIzaSyB_BgHnfwBhEQVpy9l0y8OBYXvJamzTR9E';
            $googleGeo->queryString = str_replace(" ","+",$model->street.','.$model->city.','.$model->state.' '.$model->zipcode.','.$model->country);
            $googleGeo->queryUrl = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$googleGeo->queryString.'&key='.$googleGeo->googleApiKey;
            $googleGeo->response = json_decode($curl->get($googleGeo->queryUrl));

            $returnArray['latLon']['google'] = ($googleGeo->response->results)?$googleGeo->response->results[0]->geometry->location:false;
            $allDataArray['google'] = $googleGeo->response;
//            var_dump($googleGeo->response->results[0]->geometry);

            //MapQuest operation
            $mapQuest->apiKey = 'Fmjtd%7Cluurn10b21%2C72%3Do5-9wyah4';
            $mapQuest->queryString =str_replace(" ","%20",'street='.$model->street.'&city='.$model->city.'&state='.$model->state.'&postalcode='.$model->zipcode.'&country='.$model->country);
            $mapQuest->queryUrl = 'http://www.mapquestapi.com/geocoding/v1/address?key='.$mapQuest->apiKey.'&'.$mapQuest->queryString;
            $mapQuest->response = json_decode($curl->get($mapQuest->queryUrl));
            $returnArray['latLon']['mapQuest'] = ($googleGeo->response->results)?$mapQuest->response->results[0]->locations[0]->latLng:false;
            $allDataArray['mapQuest'] = $mapQuest->response;

            //Microsoft logic
            $bingMap->apiKey = 'AjC1HSirwHxS3uUD7es9zOd3D3mMuQPjpVmtyOy2YgYOlfonUOkKXZOPc_q0wQFp';
            $bingMap->queryUrl = str_replace(" ","%20",'http://dev.virtualearth.net/REST/v1/Locations?CountryRegion='.$model->country.'&adminDistrict='.$model->state.'&locality='.$model->city.'&postalCode='.$model->zipcode.'&addressLine='.$model->street.'&key='.$bingMap->apiKey);
            $bingMap->response = json_decode($curl->get($bingMap->queryUrl));
            $returnArray['latLon']['bingMap'] = ($bingMap->response->resourceSets)?$bingMap->response->resourceSets[0]->resources[0]->point->coordinates:false;
            $allDataArray['bingMap'] = $bingMap->response;

        }
        return $this->render('geo',['model'=>$model,'data'=>$returnArray,'allData'=>$allDataArray]);
    }

    public function actionGeo2() {
        $model = new models\Geo2Form();

        if ($model->load(Yii::$app->request->post())) {

        }

        return $this->render('geo2',['model'=>$model]);
        //Scenario 1
        $point = '29 15'; //point that we need to identify
        $polygon = array("10 0", "20 0", "30 10", "30 20", "20 30", "10 30", "0 20", "0 10", "10 0"); //polygon coordinates

        var_dump(Yii::$app->PointInPolygon->pointInPolygon($point, $polygon));
        var_dump(Yii::$app->PointInPolygon->pointInsideCircle('30','25','104','30.3','24'));
    }

    public function actionPdf() {

    }

}

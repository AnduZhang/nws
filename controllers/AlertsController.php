<?php

namespace app\controllers;

use app\models\CapForm;
use linslin\yii2\curl\Curl;
use Yii;
use yii\base\Exception;
use yii\filters\AccessControl;
use app\components;
use yii\filters\VerbFilter;

use app\models;
use app\components\PointInPolygon;
use yii\data\ActiveDataProvider;
use yii\helpers\BaseUrl;


class AlertsController extends MainController
{

    public $alertId = null;

    private $_affectedProperties = null;

    public $tmpPath = 'tmp';

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
        ];
    }

    public function actionGetalertinformation()
    {
        if (Yii::$app->request->isAjax) {
            $returnArray = [];
            $post = Yii::$app->request->post();
            if (!(int)$post['id']) {
                echo json_encode(['error'=>'Yes']);
                Yii::$app->end();
            }
            $weatherAlert = models\WeatherAlertSearch::findOne($post['id']);

            $returnArray['alert'] = $weatherAlert->attributes;
            $WeatherAlertArea = models\WeatherAlertArea::find()->where(['WeatherAlert_id' => $post['id']])->one();

            $allCoordinates = models\AreaDefinition::findAll(['WeatherAlertArea_id' => $WeatherAlertArea->id]);


            foreach ($allCoordinates as $coordinateScope) {
                $returnArray['coordinates'][] = $coordinateScope->attributes;
            }

            $propertiesList = models\NRESProperty::find()->where(['status'=>models\NRESProperty::STATUS_ACTIVE])->all();
            $pointChecker = new PointInPolygon();

            $alertCircle = new \stdClass();
            $alertPolygonCoordinates = [];

            if ($post['makeRead'] == 'true') {
                $this->_markAlertReaded($weatherAlert->id);
            }

            if ($weatherAlert->type==1) { //Post-storm

                $alertCircle->radius = Yii::$app->params['postStormDefaultRadius'];
                $alertCircle->centerLat = $allCoordinates[0]->latitude;
                $alertCircle->centerLon = $allCoordinates[0]->longitude;

                $returnArray['coordinates'][0]['radius'] = $alertCircle->radius;

            } else {
                foreach ($allCoordinates as $coordinates) {

                    if ($coordinates->radius) {
                        $alertCircle->radius = $coordinates->radius; //Radius can be only one record
                        $alertCircle->centerLat = $coordinates->latitude; //Radius can be only one record
                        $alertCircle->centerLon = $coordinates->longitude; //Radius can be only one record
                    } else {
                        $alertPolygonCoordinates[] = $coordinates->latitude . ' ' . $coordinates->longitude;
                    }
                }
            }

            if (isset($alertCircle->radius)) {
                $returnArray['generalInformation']['location'] = $alertCircle->centerLat.', '.$alertCircle->centerLon;
            } else {
                $returnArray['generalInformation']['location'] = implode(',',$alertPolygonCoordinates);
            }
            $returnArray['stormPath'] = null;
            if ($weatherAlert->updates) {
                //we need to perform loop of all related alerts to this event
                $returnArray['stormPath'] = $this->_performRelatedAlertsLoop($weatherAlert);
            }

            $affectedProperties = [];


            foreach ($propertiesList as $property) {

                $isPropertyAffectedByAlert = false;

                if (isset($alertCircle->radius)) {
                    //Checking circle coordinates
                    if ($pointChecker->pointInsideCircle($alertCircle->centerLat, $alertCircle->centerLon, $alertCircle->radius, $property->latitude, $property->longitude)) {
                        $isPropertyAffectedByAlert = true;
                    }
                }  else {
                    if (!empty($alertPolygonCoordinates)) {
                        if ($pointChecker->pointInPolygon($property->latitude . ' ' . $property->longitude, $alertPolygonCoordinates) !== "outside") {
                            $isPropertyAffectedByAlert = true;
                        }
                    }
                }


                if ($isPropertyAffectedByAlert) {
                    $affectedProperties[] = $property->attributes;
                }
            }
            $returnArray['affected'] = $affectedProperties;
//            var_dump($affectedProperties);die;

            echo json_encode($returnArray);
        } else {
            throw new Exception("Forbidden");
        }
        Yii::$app->end();
    }

    private function _performRelatedAlertsLoop($alert) {

        $stormPath = array();

        $relatedAlert = models\WeatherAlert::find()->where(['identifier'=>$alert->updates])->one();


        $needToPerformLoop = true;
        while ($needToPerformLoop) {
            $stormPath[] = $this->_getAlertCoordinates($relatedAlert);
            if ($relatedAlert->updates) {
                $relatedAlert = models\WeatherAlert::find()->where(['identifier'=>$relatedAlert->updates])->one();
            } else {
                $needToPerformLoop = false;
            }
        }

        return $stormPath;

    }

    private function _getAlertCoordinates($relatedAlert) {
        $coordinatesArray = [];
        $WeatherAlertArea = models\WeatherAlertArea::find()->where(['WeatherAlert_id' => $relatedAlert->id])->one();
        $allCoordinates = models\AreaDefinition::findAll(['WeatherAlertArea_id' => $WeatherAlertArea->id]);

        if ($relatedAlert->type==1) { //Post-storm
//
//            $alertCircle->radius = Yii::$app->params['postStormDefaultRadius'];
//            $alertCircle->centerLat = $allCoordinates[0]->latitude;
//            $alertCircle->centerLon = $allCoordinates[0]->longitude;

            $coordinatesArray = ['centerLat'=>$allCoordinates[0]->latitude,'centerLon'=>$allCoordinates[0]->longitude,'radius'=>Yii::$app->params['postStormDefaultRadius']];

        } else {
            foreach ($allCoordinates as $coordinates) {

                if ($coordinates->radius) {
                    $coordinatesArray = ['centerLat'=>$coordinates->latitude,'centerLon'=>$coordinates->longitude,'radius'=>$coordinates->radius];
                } else {
                    $coordinatesArray[] = ['centerLat'=>$coordinates->latitude,'centerLon'=>$coordinates->longitude];
                }
            }
        }

        return $coordinatesArray;
    }


    public function actionAffectedpropertieslist($id = null,$makeRead = false)
    {
        if (!Yii::$app->request->isAjax && Yii::$app->request->isGet) {
            $this->redirect(['account/alerts']);
        }
        $returnArray = [];
        $affectedProperties = null;
        $weatherAlert = models\WeatherAlertSearch::findOne($id);

        if ($weatherAlert) {

            $returnArray['alert'] = $weatherAlert->attributes;
            $WeatherAlertArea = models\WeatherAlertArea::find()->where(['WeatherAlert_id' => $id])->one();

            $allCoordinates = models\AreaDefinition::findAll(['WeatherAlertArea_id' => $WeatherAlertArea->id]);
            foreach ($allCoordinates as $coordinateScope) {
                $returnArray['coordinates'][] = $coordinateScope->attributes;
            }

            $propertiesList = models\NRESProperty::find()->where(['status'=>models\NRESProperty::STATUS_ACTIVE])->all();
            $pointChecker = new PointInPolygon();

            $alertCircle = new \stdClass();
            $alertPolygonCoordinates = [];

            foreach ($allCoordinates as $coordinates) {

                if ($coordinates->radius) {
                    $alertCircle->radius = $coordinates->radius; //Radius can be only one record
                    $alertCircle->centerLat = $coordinates->latitude; //Radius can be only one record
                    $alertCircle->centerLon = $coordinates->longitude; //Radius can be only one record
                } else {
                    $alertPolygonCoordinates[] = $coordinates->latitude . ' ' . $coordinates->longitude;
                }

            }

            $affectedProperties = [];


            foreach ($propertiesList as $property) {

                $isPropertyAffectedByAlert = false;

                if (isset($alertCircle->radius)) {
                    //Checking circle coordinates
                    if ($pointChecker->pointInsideCircle($alertCircle->centerLat, $alertCircle->centerLon, $alertCircle->radius, $property->latitude, $property->longitude)) {
                        $isPropertyAffectedByAlert = true;
                    }
                }
                if (!empty($alertPolygonCoordinates)) {
                    if ($pointChecker->pointInPolygon($property->latitude . ' ' . $property->longitude, $alertPolygonCoordinates) !== "outside") {
                        $isPropertyAffectedByAlert = true;
                    }
                }
//                var_dump($isPropertyAffectedByAlert);
                if ($isPropertyAffectedByAlert) {
                    $affectedProperties[] = $property->id;
                }
            }

            if ($makeRead=='true') {
                $this->_markAlertReaded($weatherAlert->id);
            }
        }
            $IdsList = $affectedProperties?implode(',', $affectedProperties):0;
//            $provider = new ActiveDataProvider([
//                'query' => models\NRESProperty::find()->where(['status'=>models\NRESProperty::STATUS_ACTIVE])->andWhere('id IN (' . $IdsList . ')'),
//            ]);
            $searchModel = new models\NRESPropertySearch();
            $searchModel->status = models\NRESProperty::STATUS_ACTIVE;

            $dataProvider = $searchModel->search(array_merge(Yii::$app->request->queryParams,['ids'=>$IdsList]));

        $clients = $searchModel->search(array_merge(Yii::$app->request->queryParams,['ids'=>$IdsList,'orderBy'=>'client']))->getModels();
//        var_dump($clients);
        return $this->renderAjax('//account/_affected', ['dataProvider' => $dataProvider,'searchModel'=>$searchModel,'clients'=>$clients]);
    }


    //Copied from Exportalerts controller
    private function getAlertIdInformation($alertId) {
        try {

            if ($alertId) {

                $alertData = models\WeatherAlert::findOne($alertId);
                if (!$alertData) throw new Exception('Record with alertId='.$alertId.' not found in our database');
                $WeatherAlertArea = models\WeatherAlertArea::find()->where(['WeatherAlert_id' => $alertData->id])->one();
                $areaForAlertData = models\AreaDefinition::findAll(['WeatherAlertArea_id'=>$WeatherAlertArea->id]);

                $alertCircle = new \stdClass();
                $alertPolygonCoordinates = [];

                foreach ($areaForAlertData as $coordinates) {

                    if ($coordinates->radius) {
                        $alertCircle->radius = $coordinates->radius; //Radius can be only one record
                        $alertCircle->centerLat = $coordinates->latitude; //Radius can be only one record
                        $alertCircle->centerLon = $coordinates->longitude; //Radius can be only one record
                    } else {
                        $alertPolygonCoordinates[] = $coordinates->latitude. ' '. $coordinates->longitude;
                    }

                }

//                var_dump($alertPolygonCoordinates);die;
                $propertiesList = models\NRESProperty::find()->where(['status'=>models\NRESProperty::STATUS_ACTIVE])->all();
                $pointChecker = new PointInPolygon();


                $this->_affectedProperties = [];


                foreach ($propertiesList as $property) {
                    $isPropertyAffectedByAlert = false;

                    if (isset($alertCircle->radius)) {
                        //Checking circle coordinates
                        if ($pointChecker->pointInsideCircle($alertCircle->centerLat,$alertCircle->centerLon,$alertCircle->radius,$property->latitude,$property->longitude)) {
                            $isPropertyAffectedByAlert = true;
                        }
                    }

                    if (!empty($alertPolygonCoordinates)) {
                        if ($pointChecker->pointInPolygon($property->latitude.' '.$property->longitude,$alertPolygonCoordinates)!=="outside") {
                            $isPropertyAffectedByAlert = true;
                        }
                    }

                    if ($isPropertyAffectedByAlert) {
                        $this->_affectedProperties[] = $property;
                    }
                }
                if (!$this->_affectedProperties) {
                    throw new Exception("There are no properties affected by this Alert. Nothing to export");
                }

                return $alertData;
            } else {
                throw new Exception('Please choose Alert that you want to export');
            }
        } catch (\Exception $e) {
            echo json_encode(['error'=>$e->getMessage()]);

        }
        Yii::$app->end();
    }

    private function _debugAddMessage($message) {
        if ($this->_debug) {
            $this->debugInfo[] = $message;
        }
    }

    private function _writeLogLine($message) {
        Yii::info($message, 'alertExport');
        return true;
    }

    private function generatePDF($alertData) {
        $pdf = Yii::$app->pdf; // or new Pdf();
        $mpdf = $pdf->api; // fetches mpdf api
        $mpdf->SetHeader('NRESStormTracker Affected Properties for alertId = '.$alertData->id); // call methods or set any properties
        $this->layout = 'print';
        $content = $this->render('//site/pdf',['alert'=>$alertData,'affected'=>$this->_affectedProperties]);

        $filename = date('Ymd_His').'_'.$alertData->id.'.pdf';
        $mpdf->WriteHtml($content);

        echo $mpdf->Output(Yii::$app->basePath.'/web/'.$this->tmpPath.'/'.$filename, 'F'); // call the mpdf api output as needed

        return BaseUrl::base(true).'/'.$this->tmpPath.'/'.$filename;
    }



    public function actionExportcsv() {

        $post = Yii::$app->request->post();
        $get = Yii::$app->request->get();
        if (Yii::$app->request->isAjax) {
            if (isset($post['alertId']))
                $alertData = $this->getAlertIdInformation($post['alertId']);
            $filename = date('Ymd_His').'_'.$alertData->id.'.csv';
            $output = fopen(Yii::$app->basePath.'/web/'.$this->tmpPath.'/'.$filename, 'w');
            $headerArray = array('AlertID', 'AlertDate', 'AlertMagnitude','AlertSeverity','Event','PropertyID',
                'PropertyStreetAddress','PropertyCity','PropertyState','PropertyZipcode','PropertyClient','PropertyLatitude','PropertyLongitude','PropertyStatus');
            fputcsv($output, $headerArray);
            foreach ($this->_affectedProperties as $property) {
                $csvLine = [];
                $csvLine[] = $alertData->id;
                $csvLine[] = date('M d, Y H:i',$alertData->date);
                $csvLine[] = $alertData->magnitude;
                $csvLine[] = $alertData->severity;
                $csvLine[] = \app\models\WeatherAlert::getAlertTypeByEventId($alertData->event);
                $csvLine[] = $property->id;
                $csvLine[] = $property->streetAddress;
                $csvLine[] = $property->city;
                $csvLine[] = $property->state;
                $csvLine[] = $property->zipcode;
                $csvLine[] = $property->client;
                $csvLine[] = $property->latitude;
                $csvLine[] = $property->longitude;
                $csvLine[] = $property->status;
                fputcsv($output,$csvLine);
                $csvInlineOutput[] = $csvLine;
            }

            echo json_encode(['file'=>$filename]);
        } else {
            if (isset($get['file']) && file_exists(Yii::$app->basePath.'/web/'.$this->tmpPath.'/'.$get['file'])) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.basename($get['file']));
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize(Yii::$app->basePath.'/web/'.$this->tmpPath.'/'.$get['file']));
                readfile(Yii::$app->basePath.'/web/'.$this->tmpPath.'/'.$get['file']);
                Yii::$app->end();
            }
        }
    }

    public function actionExportpdf() {

        $post = Yii::$app->request->post();
        $get = Yii::$app->request->get();
        if (Yii::$app->request->isAjax) {
            if (isset($post['alertId']))
                $alertData = $this->getAlertIdInformation($post['alertId']);
            $pdf = Yii::$app->pdf; // or new Pdf();
            $mpdf = $pdf->api; // fetches mpdf api
            $mpdf->SetHeader('NRESStormTracker Affected Properties for alertId = '.$alertData->id); // call methods or set any properties
            $this->layout = 'print';
            $content = $this->render('//site/pdf',['alert'=>$alertData,'affected'=>$this->_affectedProperties]);

            $filename = date('Ymd_His').'_'.$alertData->id.'.pdf';
            $mpdf->WriteHtml($content);

            echo $mpdf->Output(Yii::$app->basePath.'/web/'.$this->tmpPath.'/'.$filename, 'F'); // call the mpdf api output as needed

            echo json_encode(['file'=>$filename]);
        } else {

            if (isset($get['file']) && file_exists(Yii::$app->basePath.'/web/'.$this->tmpPath.'/'.$get['file'])) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.basename($get['file']));
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize(Yii::$app->basePath.'/web/'.$this->tmpPath.'/'.$get['file']));

                readfile(Yii::$app->basePath.'/web/'.$this->tmpPath.'/'.$get['file']);
                Yii::$app->end();
            }
        }
    }

    public function _markAlertReaded($id) {
        $alertReaded = models\UserReadAlerts::findOne(['WeatherAlert_id'=>$id,'User_id'=>Yii::$app->user->id]);
        if ($alertReaded) {
            return false;
        }
        $alertReaded = new models\UserReadAlerts();
        $alertReaded->User_id = Yii::$app->user->id;
        $alertReaded->WeatherAlert_id = $id;
        $alertReaded->save();
        return true;
    }
}

<?php

namespace app\controllers;

use Yii;
use yii\base\Exception;
use app\models;
use yii\rest\ActiveController;
use app\components\PointInPolygon;
use yii\helpers\BaseUrl;

class ExportalertsController extends ActiveController
{
    public $modelClass = 'app\models\User';

    public $debugInfo = [];

    public $_debug = false;

    public $tmpPath = 'tmp';
    public $alertId = null;
    public $format = 'pdf';

    private $_affectedProperties = null;
    private $_errorMessage = false;

    public function actionExportrest() {
        $startTime = time();

        $this->_writeLogLine('Start Date/Time - '.date('Y-m-d\TH:i:s',$startTime));
        header('Content-type: application/json');

        try {
            $data = file_get_contents("php://input");
            $data = json_decode($data);


            if ($data && isset($data->alertId)) {
                $this->format = (isset($data->format) && $data->format)?$data->format:$this->format;
                $this->tmpPath = (isset($data->tmpPath) && $data->tmpPath)?$data->tmpPath:$this->tmpPath;

                if (!is_dir(Yii::$app->basePath.'/'.$this->tmpPath.'/')) {
                    throw new Exception('Temp directory path is invalid.');
                }

                $alertData = models\WeatherAlert::findOne($data->alertId);
                if (!$alertData) throw new Exception('Record with alertId='.$data->alertId.' not found in our database');

                $areaForAlertData = models\AreaDefinition::findAll(['WeatherAlertArea_id'=>$alertData->id]);

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
                $propertiesList = models\NRESProperty::find()->all();
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
                    throw new Exception("There are no properties affected by this Alert (".$alertData->id.")");
                }

                switch ($this->format) {
                    case 'csv':
                        $generatedFileURL = $this->generateCSV($alertData);
                        break;
                    case 'pdf':
                        $generatedFileURL = $this->generatePDF($alertData);
                        break;
                    default:
                        throw new Exception("Invalid format of output file is defined");
                }

                $this->_writeLogLine('Affected Properties - '.count($this->_affectedProperties));
                $this->_writeLogLine('Generated File URL - '.$generatedFileURL);
                $this->_writeLogLine('End Date/Time - '.date('Y-m-d\TH:i:s',time()));
                //Now we need to check if some properties are located inside this coordinates
                $responce = ['fileUrl'=>$generatedFileURL,'affectedProperties'=>count($this->_affectedProperties)];
                header('Content-type: application/json');
                echo json_encode($responce);
//                if ($this->_debug == 'true') {
//
//                    echo json_encode(array_merge(['start'=>date('Y-m-d\TH:i:s',$startTime),'end'=>date('Y-m-d\TH:i:s',$endTime)],(array)$atomGeneralInformation));
//                } else {
//                    echo json_encode(['result'=>'Job Done']);
//                }
            } else {
                throw new Exception('Missing alertId parameter');
            }
        } catch (\Exception $e) {
            echo json_encode(['error'=>$e->getMessage()]);
            $this->_writeLogLine($e->getMessage());
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

    private function generateCSV($alertData) {
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=data.csv');
        $filename = date('Ymd_His').'_'.$alertData->id.'.csv';
        $output = fopen(Yii::$app->basePath.'/web/'.$this->tmpPath.'/'.$filename, 'w');

        fputcsv($output, array('AlertID', 'AlertDate', 'AlertMagnitude','AlertSeverity','PropertyID',
            'PropertyStreetAddress','PropertyCity','PropertyState','PropertyZipcode','PropertyClient','PropertyLatitude','PropertyLongitude','PropertyStatus'));
        foreach ($this->_affectedProperties as $property) {
            $csvLine = [];
            $csvLine[] = $alertData->id;
            $csvLine[] = $alertData->date;
            $csvLine[] = $alertData->magnitude;
            $csvLine[] = $alertData->severity;
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
        }
        return BaseUrl::base(true).'/'.$this->tmpPath.'/'.$filename;

    }



}

<?php

namespace app\controllers;

use Yii;
use yii\base\Exception;
use app\models;
use yii\rest\ActiveController;
use app\helpers\FtpHelper;
use app\helpers;

class ParselsrController extends ActiveController
{
    public $modelClass = 'app\models\User';



    public $debugInfo = [];

    public $ftpHost = 'tgftp.nws.noaa.gov';
    public $ftpLogin = 'anonymous';
    public $ftpPassword = '';
    public $lsrFilesPath = '/SL.us008001/DF.c5/DC.textf/DS.lsrnw';

    public $lsrUrl = '';
    public $eventType = ['tornado'];
    public $fileNameFormat = '/sn.[0-9]{4}.txt/';

    public $_debug = false;

    public $force = false;
    private $_errorMessage = false;



    public function actionParserest() {



        header('Content-type: application/json');
        $result = array();

        $result['start'] = date('m-d-Y\TH:i:s');

        $scannedFiles = [];


        try {
            $data = file_get_contents("php://input");
            $data = json_decode($data);

            if ($data) {
                $this->ftpHost = (isset($data->ftpHost) && $data->ftpHost)?$data->ftpHost:$this->ftpHost;
                $this->ftpLogin = (isset($data->ftpLogin) && $data->ftpLogin)?$data->ftpLogin:$this->ftpLogin;
                $this->ftpPassword = (isset($data->ftpPassword) && $data->ftpPassword)?$data->ftpPassword:$this->ftpPassword;
                $this->lsrUrl = (isset($data->lsrURL) && $data->lsrURL)?$data->lsrURL:$this->lsrUrl;
                $this->eventType = (isset($data->eventType) && $data->eventType)?$data->eventType:$this->eventType;
                $this->force = (isset($data->force) && $data->force)?$data->force:$this->force;
                $this->_debug = (isset($data->_debug) && $data->_debug)?$data->_debug:$this->_debug;

                if (!is_array($this->eventType)) {
                    throw new Exception('EventType should be an array');
                } else {
                    $this->eventType = array_map('strtolower', $this->eventType);
                }
                $ftpHelper = new FtpHelper();
                $ftpHelper->ftpHost = $this->ftpHost;
                $ftpHelper->ftpLogin = $this->ftpLogin;
                $ftpHelper->ftpPassword = $this->ftpPassword;
                $ftpHelper->fileNameFormat = $this->fileNameFormat;
                $ftpHelper->filterEventType = $this->eventType;
                $ftpHelper->forceScan = $this->force;
                $filesList = $ftpHelper->connectToFtp();
                if (!$this->lsrUrl) {
                    $scannedFiles[] = $ftpHelper->performScan($filesList);
                } else {
                    $this->_writeLogLine('LSR File to scan - '.$this->lsrUrl);
                    if (in_array($this->lsrUrl, $filesList)) {
                        $scannedFiles[] = $ftpHelper->performScan([$this->lsrUrl]);

                    } else {
//                        $this->_errorMessage = "Can't find such file on FTP";
//                        $this->_writeLogLine($this->_errorMessage.'('.$this->lsrUrl.')');
                        throw new Exception("Can't find such file on FTP");
                    }

                }

                $result['end'] = date('m-d-Y\TH:i:s');
                $this->_writeLogLine('Process started - '.$result['start']);
                $this->_writeLogLine('Process ended - '.$result['end']);

                $result['insertedAlerts'] = $ftpHelper->insertedAlerts;
                $result['ignoredAlerts'] = $ftpHelper->ignoredAlerts;
                $this->_writeLogLine('Inserted alerts - '.$result['insertedAlerts']);
                $this->_writeLogLine('Ignored Alerts - '.$result['ignoredAlerts']);
                $result['files'] = $scannedFiles[0];
                echo json_encode($result);
            } else {
                throw new Exception('Missing lsrURL parameter');

            }

        } catch (\Exception $e) {
            echo json_encode(['error'=>$e->getMessage().' Line:'.$e->getLine()]);
            $this->_writeLogLine('ERROR - '.$e->getMessage());
        }


        Yii::$app->end();
    }


    private function _getCapAreaInformationInDatabse($entry) {

        try {
            $atomGeneralInformation = Yii::$app->CAPParser->getCapContent($entry[0]);
            //Simple way to create object instad of XML object
            $atomGeneralInformation = json_decode(json_encode($atomGeneralInformation));


            $alertModelSearch = new models\WeatherAlertSearch();
            $polygonInfo = isset($atomGeneralInformation->info->area->polygon)?(array)$atomGeneralInformation->info->area->polygon:NULL;
            $circleInfo = isset($atomGeneralInformation->info->area->circle)?(array)$atomGeneralInformation->info->area->circle:NULL;
            if ((!empty($polygonInfo) || !empty($circleInfo)) && $atomGeneralInformation->status == 'Actual'
                && $atomGeneralInformation->msgType=='Alert') { //We don't need to use such information without geo info
                $returnArray =
                    [
                        'identifier'=>$entry[0],
                        'polygon'=>$polygonInfo,
                        'circle'=>$circleInfo,
                        'date'=>$atomGeneralInformation->sent,
                        'event'=>$atomGeneralInformation->info->event,
                        'severity'=>$atomGeneralInformation->info->severity,
                        'type'=>0,
                    ];

                switch(strtolower($returnArray['event'])) {
                    case 'hurricane':
                        $returnArray['event'] = 0;
                        break;
                    case 'tornado':
                        $returnArray['event'] = 1;
                        break;
                    default:
                        $returnArray['event'] = 2;
                        break;
                }

                return $returnArray;
            } else {
                $this->skippedAlertsCount++;
                $this->_debugAddMessage('One alert entry skipped (It has missed information)');

            }

        } catch (\Exception $e) {
            echo json_encode(['error'=>$e->getMessage()]);
            $this->_writeLogLine($e->getMessage());
        }
        return false;
    }

    private function _debugAddMessage($message) {
        $this->_writeLogLine($message);
        if ($this->_debug) {
            $this->debugInfo[] = $message;
        }
    }

    private function _writeLogLine($message) {
        Yii::info($message, 'lsr');
        return true;
    }



}

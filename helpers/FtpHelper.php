<?php
namespace app\helpers;

use Yii;
use yii\base\Exception;
use app\models;
use app\helpers;

class FtpHelper
{


    CONST CURRENT_CATEGORY = '.';
    private $conn;

    //TODO: Need to define this variables inside config file
    public $ftpAddress = 'tgftp.nws.noaa.gov';
    public $ftpLogin = 'anonymous';
    public $ftpPassword = '';
    public $lsrFilesPath = '/SL.us008001/DF.c5/DC.textf/DS.lsrnw';
    public $fileNameFormat = '/SL.us008001/DF.c5/DC.textf/DS.lsrnw';

    public $filterEventType = "";

    public $insertedAlerts = 0;
    public $ignoredAlerts = 0;

    public $forceScan = false;


    public function connectToFtp()
    {
        return $this->_performConnect();
    }

    private function _performConnect()
    {

        try {
            $this->conn = ftp_connect($this->ftpAddress);
            ftp_login($this->conn, $this->ftpLogin, $this->ftpPassword);
            ftp_pasv($this->conn, true);
            $this->_changeDirectory($this->lsrFilesPath);
            $lsrFilesList = $this->_getContentList(FtpHelper::CURRENT_CATEGORY);

            return $lsrFilesList;

        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
    }

    public function performScan($fileNames)
    {
        $i = 0;
        $filesArray = [];


        foreach ($fileNames as $file) {
//            $i++;
            //Test code starts here
//            $fileUrl = $this->_downloadFile($file);
//            Yii::$app->LSRParser->url = $fileUrl;
//            Yii::$app->LSRParser->fileName = $file;
//            $fileArray = Yii::$app->LSRParser->getContent();

            //Test code ends here
            if (preg_match($this->fileNameFormat, $file)) {


                $lastModifiedDate = $this->_getLastModifiedDateForFile($file);

                $isFileRecordExists = models\ProcessedLSR::find()->where('fileName = :filename', [':filename' => $file])->one();

                if (($isFileRecordExists && $isFileRecordExists->modifiedDate != $lastModifiedDate) || !$isFileRecordExists || $this->forceScan) {
                    $fileUrl = $this->_downloadFile($file);
                    Yii::$app->LSRParser->url = $fileUrl;
                    Yii::$app->LSRParser->fileName = $file;
                    $fileArray = Yii::$app->LSRParser->getContent();
                    $validationString = $fileArray['state'] . ' ' . $fileArray['city'] . ' ' . $fileArray['date'] . ' ' . $fileArray['time'];

                    if ($isFileRecordExists) {
                        if ($isFileRecordExists->validationString != $validationString || $this->forceScan) { //Update required
                            $isFileRecordExists->validationString = $validationString;
                            $isFileRecordExists->modifiedDate = $lastModifiedDate;
                            $isFileRecordExists->save();
                            $this->insertNewLSRRecords($fileArray);
                        }

                    } else {
                        //We need to add a new record
                        $ProcessedLSRModel = new models\ProcessedLSR();
                        $ProcessedLSRModel->fileName = $file;
                        $ProcessedLSRModel->validationString = $validationString;
                        $ProcessedLSRModel->modifiedDate = $lastModifiedDate;
                        $ProcessedLSRModel->save();

                        $this->insertNewLSRRecords($fileArray);
                    }

                    $filesArray[] = $fileArray;
                }

                $i++;
                @unlink(\Yii::getAlias('@app') . '/tmp/' . $file);
            }
        }

        return $filesArray;
    }

    private function _getContentList($path = '/')
    {
        return ftp_nlist($this->conn, $path);
    }

    private function _changeDirectory($path)
    {
        ftp_chdir($this->conn, $path);
    }

    private function _getLastModifiedDateForFile($fileName)
    {
        return ftp_mdtm($this->conn, $fileName);
    }

    private function _downloadFile($fileName)
    {
        ftp_get($this->conn, \Yii::getAlias('@app') . '/tmp/' . $fileName, $fileName, FTP_BINARY);

        return \Yii::getAlias('@app') . '/tmp/' . $fileName;
    }

    public function log($message)
    {
        //Temporary it will work like this
        echo '<b>' . date('Y-m-d H:i:s', time()) . '   LOG: </b>' . $message;
        echo '<br />===================================================<br />';
    }

    public function insertNewLSRRecords($records)
    {
        foreach ($records['alerts'] as $record) {
            if (in_array(strtolower($record['event']),$this->filterEventType)) {

                $weatherAlertsModel = new models\WeatherAlert();
                switch (strtolower($record['event'])) {
                    case 'hurricane':
                        $weatherAlertsModel->event = 0;
                        break;
                    case 'tornado':
                        $weatherAlertsModel->event = 1;
                        break;
                    default:
                        $weatherAlertsModel->event = 2;
                        break;
                }

                $weatherAlertsModel->status = models\WeatherAlert::STATUS_ACTUAL;
                $weatherAlertsModel->type = 1;
                $weatherAlertsModel->magnitude = $record['magnitude'];
                $weatherAlertsModel->magnitudeUnit = $record['magnitudeUnit'];

                //We need to display date in needed format

                $newTime = substr_replace($record['time'], ':', 2, 0);
//                $newDate = str_replace('/', '-', $record['date']);
                $dateforConvertionArray = explode('/',$record['date']);
                $finalDateTime = $dateforConvertionArray[2].'-'.$dateforConvertionArray[0].'-'.$dateforConvertionArray[1].'T'.date("H:i:s", strtotime($newTime));

                $weatherAlertsModel->date = DateTimeHelper::createUnixtimeFromDate($finalDateTime);

                $weatherAlertsModel->save();
                $weatherAlertAreaModel = new models\WeatherAlertArea();

                $weatherAlertAreaModel->WeatherAlert_id = $weatherAlertsModel->id;

                $weatherAlertAreaModel->save();

                $areaDefinitionModel = new models\AreaDefinition();
                $areaDefinitionModel->WeatherAlertArea_id = $weatherAlertAreaModel->id;
                $areaDefinitionModel->latitude = $record['latitude'];
                $areaDefinitionModel->longitude = $record['longitude'];

                $areaDefinitionModel->save();

                $this->insertedAlerts++;

                $this->consoleLog('One alert entry added (ID: '.$weatherAlertsModel->id.')');

            } else {
                $this->ignoredAlerts++;
                $this->consoleLog('One alert entry skipped (Filtered by event type)');
            }
        }
    }

    public function consoleLog($message,$group = 'cron') {
        Yii::error($message,$group);
        Yii::info($message, $group);
        Yii::trace($message, $group);
        echo $message;
        echo "\n";
        return true;
    }


}
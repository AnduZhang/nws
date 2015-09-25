<?php

namespace app\controllers;

use Yii;
use yii\base\Exception;
use app\models;
use yii\rest\ActiveController;
use app\helpers;

class ParsealertsController extends ActiveController
{
    public $modelClass = 'app\models\User';

    public $_debug = false;

    public $debugInfo = [];

    public $addedAlertsCount = 0;
    public $skippedAlertsCount = 0;


    public function actionParserest() {

        header('Content-type: application/json');

        try {
            $data = file_get_contents("php://input");


            $data = json_decode($data);

            if ($data && isset($data->feedURL)) {


                if (isset($data->debug)) {
                    $this->_debug = $data->debug;
                }

                $atomGeneralInformation = $this->actionParse($data->feedURL,true);

                if ($this->_debug == 'true') {
                    $returnArray = ['atomInformation'=>$atomGeneralInformation,'debug'=>$this->debugInfo];
                    echo json_encode($returnArray);
                } else {
                    echo json_encode(['result'=>'Job Done']);
                }
            } else {
                throw new Exception('Missing feedURL parameter');
            }
        } catch (\Exception $e) {
            $this->_writeLogLine('Process ERROR -  '.$e->getMessage());
            echo json_encode(['error'=>$e->getMessage().' Line:'.$e->getLine()]);
        }
        Yii::$app->end();
    }
    public function actionParse($url, $fromRest = false) {



        $startTime = time();
        $needToUpdateCAP = false;
        try {
        $atomGeneralInformation = Yii::$app->CAPParser->getAtomGeneralContent($url);
        $atomEntriesContent = Yii::$app->CAPParser->getEntriesContent($url);
        if (!isset($atomGeneralInformation['updated']) || !isset($atomGeneralInformation['id'])) {
            throw new Exception("Can't parse AtomFeed file by given URL");
        }
        //TODO: Here we need to check if the alert information is new, and if needed to update our database

        $processedFeedsModel = new models\ProcessedATOMFeeds();
        $processedFeedsModelSearch = new models\ProcessedATOMFeedsSearch();

        $atomRecordInDb = $processedFeedsModelSearch->findOne(['feedURL'=>$atomGeneralInformation['id']]);


        if (!$atomRecordInDb) { //We don't have records at all for this file
            $processedFeedsModel->updated = helpers\DateTimeHelper::createUnixtimeFromDate($atomGeneralInformation['updated']);
            $processedFeedsModel->feedURL = $atomGeneralInformation['id'];

            $processedFeedsModel->save();
            $this->_debugAddMessage('Added ProcessedATOMFeeds Record, id - '.$processedFeedsModel->feedURL.', updated - '.helpers\DateTimeHelper::createDateFromUnixtime($processedFeedsModel->updated).'('.$atomGeneralInformation['updated'].')');
            $needToUpdateCAP = true;
        } else {

            if ($atomRecordInDb->updated != helpers\DateTimeHelper::createUnixtimeFromDate($atomGeneralInformation['updated'])) {
                $this->_debugAddMessage('We need to update records in WeatherAlert and WeatherAlertArea tables.Updated time in the database -'.helpers\DateTimeHelper::createDateFromUnixtime($atomRecordInDb->updated).
                    ', from file - '.$atomGeneralInformation['updated'].' ('.helpers\DateTimeHelper::createDateFromUnixtime(helpers\DateTimeHelper::createUnixtimeFromDate($atomGeneralInformation['updated'])).')');
                $atomRecordInDb->updated = helpers\DateTimeHelper::createUnixtimeFromDate($atomGeneralInformation['updated']);
                $atomRecordInDb->save();
                $needToUpdateCAP = true;

            }
        }

        if (!$needToUpdateCAP) {
            $this->_debugAddMessage('We DON\'T need to update records in WeatherAlert and WeatherAlertArea tables.');
        }
        if ($needToUpdateCAP) { //change 1 to $needToUpdateCAP
            foreach ($atomEntriesContent['entries'] as $entry) {
                $entryContent = $this->_getCapAreaInformationInDatabse($entry);
                if ($entryContent) {
                    // Now we try to find this alert and check if we have it already in our database
                    $alertModel = models\WeatherAlert::find()->where(['identifier'=>$entryContent['identifier'],'date'=>helpers\DateTimeHelper::createUnixtimeFromDate($entryContent['date'])])->one();
                    if (!$alertModel) {
                        $alertModel = new models\WeatherAlert();
                    } else {
                        $this->skippedAlertsCount++;
                        $this->_debugAddMessage('One alert entry skipped (Information about this alert is actual in our database, ID = '.$alertModel->id.')');
                        continue;
                    }

                    $alertModel->attributes = $entryContent;
                    $alertModel->date = helpers\DateTimeHelper::createUnixtimeFromDate($alertModel->date);
                    $alertModel->save();
                    $this->_debugAddMessage('One alert entry added (ID: '.$alertModel->id.')');
                    $this->addedAlertsCount++;

                    if ($alertModel->updates) {
                        //WE need to change status of old alert
                        //TODO: this part of code should be checked on real data
                        $alertModel = models\WeatherAlert::find()->where(['identifier'=>$alertModel->updates])->one();
                        $alertModel->status = models\WeatherAlert::STATUS_UPDATED;
                        $alertModel->save();

                    }

                    $weatherAlertAreaModel = new models\WeatherAlertArea();
                    $weatherAlertAreaModel->WeatherAlert_id = $alertModel->id;
                    $weatherAlertAreaModel->save();



                    if (isset($entryContent['polygon']['0'])) {
                        $coordinatePairs = explode(' ',$entryContent['polygon']['0']);
                        foreach ($coordinatePairs as $pair) {

                            $areaDefinitionModel = new models\AreaDefinition();
                            $areaDefinitionModel->WeatherAlertArea_id = $weatherAlertAreaModel->id;
                            $latLonFromPair = explode(',',$pair);
                            $areaDefinitionModel->latitude = $latLonFromPair[0];
                            $areaDefinitionModel->longitude = $latLonFromPair[1];

                            $areaDefinitionModel->save();

                        }
                    }

                    if (isset($entryContent['circle']['0'])) {
                        $coordinatePair = explode(' ',$entryContent['polygon']['0']);
                        $areaDefinitionModel = new models\AreaDefinition();
                        $areaDefinitionModel->WeatherAlertArea_id = $weatherAlertAreaModel->id;
                        $latLonFromPair = explode(',',$coordinatePair);
                        $areaDefinitionModel->latitude = $latLonFromPair[0];
                        $areaDefinitionModel->longitude = $latLonFromPair[1];
                        $areaDefinitionModel->radius = $coordinatePair[1];
                        $areaDefinitionModel->save();
                    }
                }
            }
        }
        $endTime = time();

        $this->_debugAddMessage('Start date - '.date('Y-m-d\TH:i:s',$startTime));
        $this->_debugAddMessage('End date - '.date('Y-m-d\TH:i:s',$endTime));
        $this->_debugAddMessage('Execution Time - '.($endTime - $startTime).' seconds.');
        $this->_debugAddMessage('Count of added alerts - '.$this->addedAlertsCount);
        $this->_debugAddMessage('Count of skipped alerts - '.$this->skippedAlertsCount);
        $atomGeneralInformation = array_merge(['start'=>date('Y-m-d\TH:i:s',$startTime),'end'=>date('Y-m-d\TH:i:s',$endTime)],$atomGeneralInformation);
        if (!$fromRest && $this->_debug) {
            foreach ($this->debugInfo as $line) {
                echo $line."<br />";
                echo "##########################################################################################<br />";
            }
            Yii::$app->end();
        }
        $atomGeneralInformation['entries'] = isset($atomEntriesContent['entries'])?$atomEntriesContent['entries']:[];
        return $atomGeneralInformation;

        } catch (\Exception $e) {
            $this->_writeLogLine('Process ERROR -  '.$e->getMessage());
            echo json_encode(['error'=>$e->getMessage().' Line:'.$e->getLine()]);
            Yii::$app->end();
        }
    }

    private function _getCapAreaInformationInDatabse($entry) {

        try {

            $atomGeneralInformation = Yii::$app->CAPParser->getCapContent($entry[0]);
            //Simple way to create object instad of XML object
            $atomGeneralInformation = json_decode(json_encode($atomGeneralInformation));

            $polygonInfo = isset($atomGeneralInformation->info->area->polygon)?(array)$atomGeneralInformation->info->area->polygon:NULL;
            $circleInfo = isset($atomGeneralInformation->info->area->circle)?(array)$atomGeneralInformation->info->area->circle:NULL;
            if ((!empty($polygonInfo) || !empty($circleInfo)) && $atomGeneralInformation->status == 'Actual'
                && ($atomGeneralInformation->msgType=='Alert' || $atomGeneralInformation->msgType=='Update')) { //We don't need to use such information without geo info

                $returnArray =
                    [
                        'identifier'=>$atomGeneralInformation->identifier,
                        'polygon'=>$polygonInfo,
                        'circle'=>$circleInfo,
                        'date'=>$atomGeneralInformation->sent,
                        'event'=>$atomGeneralInformation->info->event,
                        'severity'=>$atomGeneralInformation->info->severity,
                        'type'=>0,
                        'msgType'=>models\WeatherAlert::assignMsgType($atomGeneralInformation->msgType),
                    ];

                $returnArray['status'] = models\WeatherAlert::STATUS_ACTUAL;

                if ($returnArray['msgType']==models\WeatherAlert::MSG_TYPE_UPDATED) {
                    if (isset($atomGeneralInformation->references)) {
                        $referencesArray = explode(',',$atomGeneralInformation->references);
                        $returnArray['updates'] = trim($referencesArray[1]); // Logic for updated references
                    }
                }

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
            $this->_writeLogLine('Process ERROR -  '.$e->getMessage());
            echo json_encode(['error'=>$e->getMessage()]);
        }
        return false;
    }

    public function actionParsecap() {
        $startTime = time();
        header('Content-type: application/json');

        try {
            $data = file_get_contents("php://input");
            $data = json_decode($data);


            if ($data && isset($data->capURL)) {
                $data->eventType = isset($data->eventType)?$data->eventType:['hurricane'];
                if (!is_array($data->eventType)) {
                    throw new Exception('EventType should be an array');
                }
                $this->_writeLogLine('CAP file URL - '.$data->capURL,'cap');
                $this->_writeLogLine('Start Date/Time - '.date('Y-m-d\TH:i:s',$startTime),'cap');
                if (isset($data->debug)) {
                    $this->_debug = $data->debug;
                }

                //Here we need to read and parse Alerts

                $capGeneralInformation = Yii::$app->CAPParser->getCapContent($data->capURL);

                foreach ($capGeneralInformation->info as $entry) {
                    if (in_array(strtolower((string)$entry->event),$data->eventType) && (string)$entry->status=='Actual' && (string)$entry->msgType=='Alert') {
                        $this->addedAlertsCount++;
                        $this->_writeLogLine('One alert entry should be added','cap');
                    } else {
                        $this->skippedAlertsCount++;
                        $this->_writeLogLine('One alert entry skipped (It has missed information)','cap');
                    }

                    //$entryContent = $this->_getCapAreaInformationInDatabse($entry);
                }
                $this->_writeLogLine('Scan complete ('.$data->capURL.')','cap');

                $endTime = time();
                //TODO: Here we need to check if the alert information is new, and if needed to update our database
                $this->_writeLogLine('Count of added entries - '.$this->addedAlertsCount,'cap');
                $this->_writeLogLine('Count of skipped entries - '.$this->skippedAlertsCount,'cap');
                $this->_writeLogLine('End Date/Time - '.date('Y-m-d\TH:i:s',$endTime),'cap');
                if ($this->_debug == 'true') {

                    echo json_encode(array_merge(['start'=>date('Y-m-d\TH:i:s',$startTime),'end'=>date('Y-m-d\TH:i:s',$endTime)],(array)$capGeneralInformation));
                } else {
                    echo json_encode(['result'=>'Job Done']);
                }
            } else {
                throw new Exception('Missing capURL parameter');
            }
        } catch (\Exception $e) {
            $this->_writeLogLine('Process ERROR -  '.$e->getMessage(),'cap');
            echo json_encode(['error'=>$e->getMessage()]);
        }
        Yii::$app->end();
    }

    private function _debugAddMessage($message) {
        $this->_writeLogLine($message);
        if ($this->_debug) {
            $this->debugInfo[] = $message;
        }
    }

    private function _writeLogLine($message,$group = 'alerts') {
        Yii::info($message, $group);
        return true;
    }



}

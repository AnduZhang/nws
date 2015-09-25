<?php

namespace app\commands;
use app\models;
use yii\console\Controller;
use Yii;
use app\helpers;
use yii\base\Exception;




class CronController extends Controller {

    //Alerts

    public $_debug = false;

    public $debugInfo = [];

    public $addedAlertsCount = 0;
    public $skippedAlertsCount = 0;

    public function actionIndex() {
        if (Yii::$app->params['AtomFeedParser']['enabled']) {
            $this->parseAtomFeed();
            $this->consoleLog("Atom Feed Parser Enabled");
        } else {
            $this->consoleLog("Atom Feed Parser DISABLED");
        }
        if (Yii::$app->params['LsrParser']['enabled']) {
            $this->parseLsrFeed();
            $this->consoleLog("LSR Parser Enabled");
        } else {
            $this->consoleLog("LSR Parser DISABLED");
        }
        \Yii::$app->end();
    }

    public function consoleLog($message,$group = 'cron') {
        Yii::error($message,$group);
        Yii::info($message, $group);
        Yii::trace($message, $group);
        echo $message;
        echo "\n";
        return true;
    }

    public function parseAtomFeed(){

        $url = Yii::$app->params['AtomFeedParser']['url'];
        $startTime = time();
        $needToUpdateCAP = false;
        try {
            $atomGeneralInformation = Yii::$app->CAPParser->getAtomGeneralContent($url);
            $atomEntriesContent = Yii::$app->CAPParser->getEntriesContent($url);
            if (!isset($atomGeneralInformation['updated']) || !isset($atomGeneralInformation['id'])) {
                throw new Exception("Can't parse AtomFeed file by given URL");
            }

            $processedFeedsModel = new models\ProcessedATOMFeeds();
            $processedFeedsModelSearch = new models\ProcessedATOMFeedsSearch();

            $atomRecordInDb = $processedFeedsModelSearch->findOne(['feedURL'=>$atomGeneralInformation['id']]);


            if (!$atomRecordInDb) { //We don't have records at all for this file
                $processedFeedsModel->updated = helpers\DateTimeHelper::createUnixtimeFromDate($atomGeneralInformation['updated']);
                $processedFeedsModel->feedURL = $atomGeneralInformation['id'];

                $processedFeedsModel->save();
                $this->consoleLog('Added ProcessedATOMFeeds Record, id - '.$processedFeedsModel->feedURL.', updated - '.helpers\DateTimeHelper::createDateFromUnixtime($processedFeedsModel->updated).'('.$atomGeneralInformation['updated'].')');
                $needToUpdateCAP = true;
            } else {

                if ($atomRecordInDb->updated != helpers\DateTimeHelper::createUnixtimeFromDate($atomGeneralInformation['updated'])) {
                    $this->consoleLog('We need to update records in WeatherAlert and WeatherAlertArea tables.Updated time in the database -'.helpers\DateTimeHelper::createDateFromUnixtime($atomRecordInDb->updated).
                        ', from file - '.$atomGeneralInformation['updated'].' ('.helpers\DateTimeHelper::createDateFromUnixtime(helpers\DateTimeHelper::createUnixtimeFromDate($atomGeneralInformation['updated'])).')');
                    $atomRecordInDb->updated = helpers\DateTimeHelper::createUnixtimeFromDate($atomGeneralInformation['updated']);
                    $atomRecordInDb->save();
                    $needToUpdateCAP = true;

                }
            }

            if (!$needToUpdateCAP) {
                $this->consoleLog('We DON\'T need to update records in WeatherAlert and WeatherAlertArea tables.');
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
                            $this->consoleLog('One alert entry skipped (Information about this alert is actual in our database, ID = '.$alertModel->id.')');
                            continue;
                        }

                        $alertModel->attributes = $entryContent;

                        $alertModel->date = helpers\DateTimeHelper::createUnixtimeFromDate($alertModel->date);
                        $alertModel->save();
                        $this->consoleLog('One alert entry added (ID: '.$alertModel->id.')');
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

            $this->consoleLog('Start date - '.date('Y-m-d\TH:i:s',$startTime));
            $this->consoleLog('End date - '.date('Y-m-d\TH:i:s',$endTime));
            $this->consoleLog('Execution Time - '.($endTime - $startTime).' seconds.');
            $this->consoleLog('Count of added alerts - '.$this->addedAlertsCount);
            $this->consoleLog('Count of skipped alerts - '.$this->skippedAlertsCount);
            $atomGeneralInformation = array_merge(['start'=>date('Y-m-d\TH:i:s',$startTime),'end'=>date('Y-m-d\TH:i:s',$endTime)],$atomGeneralInformation);
            $atomGeneralInformation['entries'] = isset($atomEntriesContent['entries'])?$atomEntriesContent['entries']:[];
            return $atomGeneralInformation;

        } catch (\Exception $e) {
            $this->consoleLog('Process ERROR -  '.$e->getMessage());
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
                if (!in_array(strtolower($returnArray['event']),Yii::$app->params['AtomFeedParser']['filter'])) {
                    $this->skippedAlertsCount++;
                    $this->consoleLog('One alert entry skipped (Filtered by event type)');
                    return false;
                }
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
                $this->consoleLog('One alert entry skipped (It has missed information)');

            }

        } catch (\Exception $e) {
            $this->consoleLog('Process ERROR -  '.$e->getMessage());
//            echo json_encode(['error'=>$e->getMessage()]);
        }
        return false;
    }


    //Start moving LSR parser

    public function parseLsrFeed() {

        $result = array();
        $result['start'] = date('m-d-Y\TH:i:s');
        $scannedFiles = [];

        try {
            $data = Yii::$app->params['LsrParser'];
            if ($data) {

                if (!is_array($data['filter'])) {
                    throw new Exception('EventType should be an array');
                } else {
                    $filter = array_map('strtolower', $data['filter']);
                }


                $ftpHelper = new helpers\FtpHelper();
                $ftpHelper->ftpHost = $data['ftpHost'];
                $ftpHelper->ftpLogin = $data['ftpLogin'];
                $ftpHelper->ftpPassword = $data['ftpPassword'];
                $ftpHelper->fileNameFormat = $data['fileNameFormat'];
                $ftpHelper->filterEventType = $filter;
                $ftpHelper->forceScan = $data['force'];
                $filesList = $ftpHelper->connectToFtp();

                if (!$data['files']) {
                    $scannedFiles[] = $ftpHelper->performScan($filesList);
                } else {
                    $this->consoleLog('LSR File to scan - '.$this->lsrUrl);
                    if (in_array($data['files'], $filesList)) {
                        $scannedFiles[] = $ftpHelper->performScan([$data['files']]);

                    } else {
//                        $this->_errorMessage = "Can't find such file on FTP";
//                        $this->consoleLog($this->_errorMessage.'('.$this->lsrUrl.')');
                        throw new Exception("Can't find such file on FTP");
                    }

                }

                $result['end'] = date('m-d-Y\TH:i:s');
                $this->consoleLog('Process started - '.$result['start']);
                $this->consoleLog('Process ended - '.$result['end']);

                $result['insertedAlerts'] = $ftpHelper->insertedAlerts;
                $result['ignoredAlerts'] = $ftpHelper->ignoredAlerts;
                $this->consoleLog('Inserted alerts - '.$result['insertedAlerts']);
                $this->consoleLog('Ignored Alerts - '.$result['ignoredAlerts']);
                $result['files'] = $scannedFiles[0];
//                echo json_encode($result);
            } else {
                throw new Exception('Missing lsrURL parameter');

            }

        } catch (\Exception $e) {
//            echo json_encode(['error'=>$e->getMessage().' Line:'.$e->getLine()]);
            $this->consoleLog('ERROR - '.$e->getMessage());
            $this->consoleLog('LINE - '.$e->getLine());
            $this->consoleLog('FILE - '.$e->getFile());
        }


        Yii::$app->end();
    }
}
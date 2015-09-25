<?php
namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\web\UploadedFile;
use app\helpers\GoogleAPIHelper;


class UploadForm extends Model
{
    public $file;
    public $log;

    public function attributeLabels()
    {
        return [
            'file'      => \Yii::t('user', 'CSV'),

        ];
    }

    public function rules()
    {
        return [
            [['file'], 'required'],
            [['file'], 'file', 'extensions' => ['csv'], 'mimeTypes' => 'text/csv, application/vnd.ms-excel, application/force-download'],
//            [['file'], 'safe'],


        ];
    }

    public function processCsv($file) {

        ini_set('auto_detect_line_endings', true);

        try {
        $uploadedFile = Yii::$app->basePath.'/uploads/' . $file->baseName . '.' . $file->extension;
        $fileSize = $file->size;
        $delimiter = ",";
        $propertiesFromCSV = [];
        if (($handle = fopen($uploadedFile, 'r')) !== FALSE) {
            $csvFirstLine = fgetcsv($handle, 0, $delimiter);
            if (!$csvFirstLine) {
                $this->_log(1,'Wrong CSV file format (can\'t read header information)');
                echo json_encode($this->log);
                Yii::$app->end();
            }
            if (count($csvFirstLine)!=7) {
                $this->_log(1,'Wrong CSV file format (count of headers should be equal 7)');
                echo json_encode($this->log);
                Yii::$app->end();
            }
            $lineNumber = 2;
            while (($row = fgetcsv($handle, $fileSize, $delimiter)) !== FALSE) {
                $newProperty = new \stdClass();
                $newProperty->line = $lineNumber;
                foreach($row as $key=>$element) {
                    $newProperty->$csvFirstLine[$key] = $element;

                }
                $propertiesFromCSV[] = $newProperty;
                $lineNumber++;
            }
        }

        //Now we need to identify lat and lon for each record
        $googleAPIHelper = new GoogleAPIHelper();
        foreach ($propertiesFromCSV as $property) {
            if (!isset($property->streetAddress) || !isset($property->city) || !isset($property->state) || !isset($property->zipcode)) {
                throw new Exception('CSV has missing parameters (Check headers line)');
            }
            //Special check for our QA

            $propertyCheck = new NRESProperty();
            $propertyCheck->attributes = (array)$property;
            $propertyCheck->status = NRESProperty::STATUS_ACTIVE;

            if (!$propertyCheck->validate()) {

                $modelValidationErrors = [];
                foreach ($propertyCheck->getErrors() as $err) {
                    $modelValidationErrors[] = $err[0];
                }

                $this->_log($property->line,"ERROR, NOT inserted (".implode(',',$modelValidationErrors).")");
                continue;
            }

            $coordinates = $googleAPIHelper->identifyLatLon([$property->streetAddress,$property->city,$property->state,$property->zipcode]);
            if ($coordinates) {
                $property->latitude = $coordinates->lat;
                $property->longitude = $coordinates->lng;

                if ($property->id) {
                    //It's a record that we already have in our database
                    $propertyFromTheDatabase = new NRESProperty();
                    $recordInDb = $propertyFromTheDatabase->findOne($property->id);

                    if ($recordInDb) {
                        $recordInDb->attributes = (array)$property;
                        if ($recordInDb->save()) {
                            $this->_log($property->line,"SUCCESS, Updated in the database, ID #".$recordInDb->id);
                        } else {
                            $errorArray = [];
                            foreach ($recordInDb->getErrors() as $error) {
                                $errorArray[] = $error[0];
                            }
                            $this->_log($property->line,"ERROR, NOT updated, ID #".$recordInDb->id.", Errors: (".implode(',',$errorArray).")");
                        }
                    } else {
                        $this->_log($property->line,"ERROR, Record with ID #".$property->id.", does not exist.");
                    }
                } else { //absolutelly new one
                    $propertyNewModel = new NRESProperty();
                    $propertyNewModel->attributes = (array)$property;
                    $propertyNewModel->status = NRESProperty::STATUS_ACTIVE;
                    unset($propertyNewModel->id);

                    if ($propertyNewModel->save()) {
                        $this->_log($property->line,"SUCCESS, Added in the database, ID #".$propertyNewModel->id);
                    } else {
                        $errorArray = [];
                        foreach ($propertyNewModel->getErrors() as $error) {
                            $errorArray[] = $error[0];
                        }
                        $this->_log($property->line,"ERROR, NOT inserted (".implode(',',$errorArray).")");
                    }
                }
            } else { // We need to reject such properties
                $this->_log($property->line,"ERROR, Can't detect geolocation information about provided address");
            }

        }
//        var_dump($this->log);die;
        echo json_encode((object)$this->log);

//        die;
        } catch (Exception $e){
            $this->_log(0,$e->getMessage());
            echo json_encode((object)$this->log);
        }
        Yii::$app->end();
    }

    private function _log($line,$message) {
        $this->log[$line] = $message;
        return true;
    }


}
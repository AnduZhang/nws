<?php
namespace app\models;

use app\components\PointInPolygon;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\web\UploadedFile;
use app\helpers\GoogleAPIHelper;
use yii\db\Query;


class MockForm extends Model
{
    public $countPreStorm;
    public $countPostStorm;
    public $countPostAffected;
    public $countPreAffected;
    public $countStormPath;
    public $countAffectedInStormPath;
    public $cleanDb;
    public $startCoordinates;

    public $currentJob = 0; //Pre-storm
    public $isStormPath = false; //Pre-storm

    public function attributeLabels()
    {
        return [
            'file'      => \Yii::t('user', 'CSV'),

        ];
    }

    public function rules()
    {
        return [
            [['countPreStorm', 'countPostStorm','countPostAffected','countPreAffected','countStormPath','countAffectedInStormPath','startCoordinates'], 'required'],
            [['countPreStorm', 'countPostStorm','countPostAffected','countPreAffected','countStormPath','countAffectedInStormPath'], 'integer'],
            ['cleanDb','boolean'],
//            [['file'], 'file', 'extensions' => ['csv'], 'mimeTypes' => 'text/csv, application/vnd.ms-excel, application/force-download'],
            [['countPreStorm', 'countPostStorm','countPostAffected','countPreAffected','countStormPath','countAffectedInStormPath','cleanDb'], 'safe'],
        ];
    }

    public function cleanDatabase() {

        try {
            AreaDefinition::deleteAll();
            UserReadAlerts::deleteAll();
            WeatherAlertCoordinates::deleteAll();
            WeatherAlertArea::deleteAll();
            WeatherAlert::deleteAll();
            NRESProperty::deleteAll();

        } catch (\Exception $e){

        }

        return true;

    }

    public function generatePreStorm() {

        if ((int)$this->countPreStorm) {
            for($i=0;$i<(int)$this->countPreStorm;$i++) {
                $preStormModel = new WeatherAlert();
                $preStormModel->type = 0; //Pre-storm
                $preStormModel->status = WeatherAlert::STATUS_ACTUAL;
                $preStormModel->date = time();
                $preStormModel->event = rand(0,1);
                $preStormModel->msgType = 1;
                $preStormModel->severity = 'Moderate';
                $preStormModel->save();

                $weatherAlertArea = new WeatherAlertArea();

                $weatherAlertArea->WeatherAlert_id = $preStormModel->id;
                $weatherAlertArea->save();

                $this->_generateCoordinates($weatherAlertArea->id,false, rand(3,6));

            }
        }

        if ((int)$this->countPostStorm) {
            for($i=0;$i<(int)$this->countPostStorm;$i++) {

                $preStormModel = new WeatherAlert();
                $preStormModel->type = 1; //Pre-storm
                $preStormModel->status = WeatherAlert::STATUS_ACTUAL;
                $preStormModel->date = time();
                $preStormModel->event = rand(0,1);
                $preStormModel->msgType = 1;
                $preStormModel->magnitude = rand(1,10);
                $preStormModel->save();

                $weatherAlertArea = new WeatherAlertArea();

                $weatherAlertArea->WeatherAlert_id = $preStormModel->id;
                $weatherAlertArea->save();

                $this->_generateCoordinates($weatherAlertArea->id,true);


            }
        }

        if ((int)$this->countStormPath) {
            $this->isStormPath = true;
            for($i=0;$i<(int)$this->countStormPath;$i++) {
                //generate pre-storm path

                $stormCount = rand(3,6);
                $identifierOfUpdates = null;
                $event = rand(0,1);
                for($j=0;$j<$stormCount;$j++) {

                    $isLast = ($stormCount - 1 - $j)?false:true;

                    $preStormModel = new WeatherAlert();
                    $preStormModel->status = WeatherAlert::STATUS_UPDATED;
                    $preStormModel->type = 0; //Pre-storm
                    if ($isLast) {
                        $preStormModel->status = WeatherAlert::STATUS_ACTUAL;
                    }

                    $preStormModel->date = time();
                    $preStormModel->event = $event;
                    $preStormModel->identifier = 'identifier'.($j);
                    $preStormModel->severity = 'Moderate';
                    $preStormModel->msgType = 1;
                    $preStormModel->updates = $identifierOfUpdates;
                    $preStormModel->save();
                    $identifierOfUpdates = $preStormModel->identifier;
                    $weatherAlertArea = new WeatherAlertArea();

                    $weatherAlertArea->WeatherAlert_id = $preStormModel->id;
                    $weatherAlertArea->save();

                    $this->_generateCoordinates($weatherAlertArea->id,false);
                }


            }
        }

    }

    private function _generateRand($isAlert= false,$propertyBase=100) {
        $base = $isAlert?300:100;
        if (rand(0,1)) {
            return mt_rand(0, $base) / $propertyBase;
        } else {
            return -mt_rand(0, $base) / $propertyBase;
        }

    }

    private function _generateCoordinates($weatherAlertAreaId,$isCircle = false,$count = 4) {
        $count = 3;
        $areaDefinitionFirst = null;
        $areaDefinition = new AreaDefinition();
        $areaDefinition->WeatherAlertArea_id = $weatherAlertAreaId;
        $startPoint = explode(' ',$this->startCoordinates);
        if ($isCircle) {
            $areaDefinition->radius = Yii::$app->params['postStormDefaultRadius'];
            $areaDefinition->latitude = $startPoint[0] + $this->_generateRand(true);
            $areaDefinition->longitude = $startPoint[1] + $this->_generateRand(true);
            //Need to generate circle
            $areaDefinition->save();


        } else {
            for ($i=0;$i<$count;$i++) {
                $areaDefinition = new AreaDefinition();
                $areaDefinition->WeatherAlertArea_id = $weatherAlertAreaId;
                $areaDefinition->radius = null;
                $areaDefinition->latitude = $startPoint[0] + $this->_generateRand();
                $areaDefinition->longitude = $startPoint[1] + $this->_generateRand();
                if ($i == 0) {
                    $areaDefinitionFirst = $areaDefinition;//We need to close the polygon
                }
                $areaDefinition->save();



            }
            $areaDefinition = new AreaDefinition();
            $areaDefinition->attributes = $areaDefinitionFirst->attributes;
            $areaDefinition->id = NULL;
            $areaDefinition->save();
//            var_dump($areaDefinition->save());die;
        }


        $this->_createAffectedProperties($weatherAlertAreaId);

        return true;
    }

    private function _createAffectedProperties($weatherAlertAreaId) {


        $areaDefinition = AreaDefinition::find()->where(['WeatherAlertArea_id'=>$weatherAlertAreaId])->all();
        $pointChecker = new PointInPolygon();

        if (count($areaDefinition) && count($areaDefinition)==1) {
            $needToInsertProperties = (int)$this->countPostAffected;

            $alertCircle = $areaDefinition[0];

            while ($needToInsertProperties) {

                $property = new \stdClass();
                $property->latitude = $alertCircle->latitude + $this->_generateRand(false,rand(300,1000));
                $property->longitude =$alertCircle->longitude + $this->_generateRand(false,rand(300,1000));

                if ($pointChecker->pointInsideCircle($alertCircle->latitude, $alertCircle->longitude, $alertCircle->radius, $property->latitude, $property->longitude)) {
                    //insert property

                    $newProperty = new NRESProperty();
                    $newProperty->attributes = (array)$property;
                    $newProperty->name = 'Generated';
                    $newProperty->zipcode = '12345-6789';
                    $newProperty->city = 'Test';
                    $newProperty->state = 'CA';
                    $newProperty->streetAddress = 'Test';
                    $newProperty->client = 'Test';
                    $newProperty->status = 2;

                    $newProperty->save();
                    if ($newProperty->getErrors()) {
                        var_dump($newProperty->getErrors());die;
                    }
                    $needToInsertProperties--;

                }
            }


        } else {
            $allCoordinates = [];
            $needToInsertProperties = $this->isStormPath?$this->countAffectedInStormPath:(int)$this->countPreAffected;
            foreach($areaDefinition as $point) {
                $allCoordinates[] = $point->latitude . ' ' . $point->longitude;
            }

            while ($needToInsertProperties) {

                $propertyToGet = rand(0,(count($areaDefinition) - 1));
                $property = new \stdClass();
                $property->latitude = $areaDefinition[$propertyToGet]->latitude + $this->_generateRand();
                $property->longitude = $areaDefinition[$propertyToGet]->longitude + $this->_generateRand();
                if ($pointChecker->pointInPolygon($property->latitude . ' ' . $property->longitude, $allCoordinates) !== "outside") {
                    //insert property
                    $newProperty = new NRESProperty();
                    $newProperty->attributes = (array)$property;
                    $newProperty->name = 'Generated';
                    $newProperty->zipcode = '12345-6789';
                    $newProperty->city = 'Test';
                    $newProperty->state = 'CA';
                    $newProperty->streetAddress = 'Test';
                    $newProperty->client = 'Test';
                    $newProperty->status = 2;

                    $newProperty->save();

                    $needToInsertProperties--;
                }


            }

        }
    }

}
<?php
namespace app\helpers;

use Yii;
use yii\base\Exception;
use app\models;
use linslin\yii2\curl\Curl;

class GoogleAPIHelper
{
    private $apiKey = null;

    function __construct() {
        if (!$this->apiKey && !Yii::$app->params['googleAPIKey']) {
            throw new Exception("Google API Key is missing");
        } else {
            $this->apiKey = Yii::$app->params['googleAPIKey'];
        }
    }


    public function identifyLatLon($data) {
        $googleGeo = new \stdClass();
        if (count($data)!=4) {
            throw new Exception("Missing data for Google API");
        }
        $Geodata = new \stdClass();
        $Geodata->street = $data[0];
        $Geodata->city = $data[1];
        $Geodata->state = $data[2];
        $Geodata->zipcode = $data[3];
        $Geodata->country = 'USA';

        $curl = new Curl();

        //Start google operation
        $googleGeo->googleApiKey = $this->apiKey;
        $googleGeo->queryString = str_replace(" ","+",$Geodata->street.','.$Geodata->city.','.$Geodata->state.' '.$Geodata->zipcode.','.$Geodata->country);
        $googleGeo->queryUrl = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$googleGeo->queryString.'&key='.$googleGeo->googleApiKey;
        $googleGeo->response = json_decode(file_get_contents($googleGeo->queryUrl));
//        $test = $curl->get($googleGeo->queryUrl);

        $return = (isset($googleGeo->response->results) && $googleGeo->response->results)?$googleGeo->response->results[0]->geometry->location:false;
//        $return = ($googleGeo->response->results)?$googleGeo->response->results[0]->geometry->location:false;
        return $return;
    }

}
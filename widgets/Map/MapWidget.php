<?php
namespace app\widgets\map;
use yii\base\Widget;

use yii\helpers\Html;


class MapWidget extends Widget{

    public $message;

    public function init(){

        parent::init();

    }

    public function run() {
        $jsonFile = fopen(\Yii::getAlias('@app')."/runtime/map.json",'r');
        $jsonText = $this->EscapeAposAndQuotes(json_encode(json_decode(fread($jsonFile,filesize(\Yii::getAlias('@app')."/runtime/map.json")))));
        return $this->render('map',['json'=>$jsonText]);
    }

    public function EscapeAposAndQuotes($s)
    {
        $s = preg_replace('/\'/', '\\\'', $s);
        $s = preg_replace('/\"/', '\\"', $s);

        return $s;
    }
}
?>

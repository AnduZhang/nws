<?php
namespace app\widgets\alerts;

use yii\base\Widget;
use app\models;


class AlertsWidget extends Widget{

    public function init(){

        parent::init();

    }

    public function run() {

        $alertsModel = new models\WeatherAlert();

        return $this->render('alerts');
    }

}
?>

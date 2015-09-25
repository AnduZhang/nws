<?php
namespace app\helpers;

use Yii;
use yii\base\Exception;
use app\models;

abstract class DateTimeHelper
{
    public static function createUnixtimeFromDate($dateString) {


        $needToProcessTimezone = false;
        $dateAndTimeArray = explode('T',$dateString);
        $dateExploded = explode('-',$dateAndTimeArray[0]);
        $timeExploded = explode(':',$dateAndTimeArray[1]);

        if (count($timeExploded)>3) { //Time with the timezone given
            $timeExploded = explode(':',substr($dateAndTimeArray[1],0,8));

            $needToProcessTimezone = true;
        }

        $timeFromDate = gmmktime((int)$timeExploded[0],(int)$timeExploded[1],(int)$timeExploded[2],$dateExploded[1],$dateExploded[2],$dateExploded[0]);

        if ($needToProcessTimezone) {
            $timezoneExploded = explode(':',str_replace(substr($dateAndTimeArray[1],0,8),'',$dateAndTimeArray[1]));
            $timezoneOffset = $timezoneExploded[0]*3600;
            if ($timezoneOffset>0) {
                $timezoneOffset+= ((int)$timezoneExploded[0])*60;
            } else {
                $timezoneOffset-= ((int)$timezoneExploded[1])*60;
            }

            $timeFromDate-=$timezoneOffset;
        }
        return $timeFromDate;
    }

    public static function createDateFromUnixtime($date, $format = 'Y-m-d\TH:i:s') {
        return date($format,$date);
    }
}
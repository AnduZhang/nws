<?php

namespace app\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "WeatherAlert".
 *
 * @property integer $id
 * @property string $date
 * @property integer $event
 * @property integer $type
 * @property string $magnitude
 * @property string $severity
 * @property string $identifier
 *
 * @property WeatherAlertArea[] $weatherAlertAreas
 * @property WeatherAlertCoordinates $weatherAlertCoordinates
 */
class WeatherAlert extends \yii\db\ActiveRecord
{

    public $stormName;

    const MSG_TYPE_ALERT = 1;
    const MSG_TYPE_UPDATED = 2;
    const MSG_TYPE_CANCELLED = 3;

    const STATUS_ACTUAL = 1;
    const STATUS_UPDATED = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'WeatherAlert';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'event', 'type'], 'required'],
            [['date', 'event', 'type', 'msgType', 'status'], 'integer'],
            [['magnitude'], 'number'],
            [['identifier', 'updates'], 'string'],
            [['severity','magnitudeUnit'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'event' => 'Event',
            'type' => 'Type',
            'magnitude' => 'Magnitude',
            'severity' => 'Severity',
            'identifier' => 'Identifier',
            'magnitudeUnit' => 'Magnitude Unit',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWeatherAlertAreas()
    {
        return $this->hasMany(WeatherAlertArea::className(), ['WeatherAlert_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWeatherAlertCoordinates()
    {
        return $this->hasOne(WeatherAlertCoordinates::className(), ['WeatherAlert_id' => 'id']);
    }

    public function getUserReadAlerts()
    {
        return $this->hasMany(UserReadAlerts::className(), ['WeatherAlert_id' => 'id']);
    }

//    public function afterSave($insert, $changedAttributes)
//    {
//
////        if ($insert) {
////            //New weatherAlert added
////            $allUsers = User::find()->all();
////            foreach ($allUsers as $user) {
////                $userReadModel = new UserReadAlerts();
////                $userReadModel->User_id = $user->id;
////                $userReadModel->isRead = 0; //New alert
////                $userReadModel->WeatherAlert_id = $this->id;
////                $userReadModel->save();
////            }
////        }
//
//
//    }

    public function getUnreadAlertsCount($type) {
        $query = new Query();
        $ids = [];

        $timePeriodForAlert = ($type==0)?Yii::$app->params['timePeriodForRecentPreAlerts']:Yii::$app->params['timePeriodForRecentPostAlerts'];
        $allAlerts = WeatherAlert::find()->where(['type'=>$type,'status'=>WeatherAlert::STATUS_ACTUAL])->andFilterWhere(['>','date',time() - $timePeriodForAlert*3600])->all();
        foreach ($allAlerts as $alert) {
            $ids[] = $alert->id;
        }
//        var_dump($ids);die;
//        $readedAlerts = UserReadAlerts::find()->andFilterWhere(['in','WeatherAlert_id',implode(',',$ids)])->all();
        $readedAlerts = UserReadAlerts::find()->where(['WeatherAlert_id' => $ids,'User_id'=>Yii::$app->user->id])->count();
        return count($ids) - (int)$readedAlerts;
    }

    public static function assignMsgType($type) {

        switch ($type) {
            case 'Alert':
                return WeatherAlert::MSG_TYPE_ALERT;
            break;
            case 'Update':
                return WeatherAlert::MSG_TYPE_UPDATED;
                break;
            case 'Cancel':
                return WeatherAlert::MSG_TYPE_CANCELLED;
                break;
            default:
                return 0;
            break;
        }
    }

    public static function getAlertTypeByEventId($id) {
        switch ($id) {
            case 0:
                return 'Hurricane';
                break;
            case 1:
                return 'Tornado';
                break;
            default:
                return 'Other';
                break;
        }
    }
}

<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "WeatherAlertCoordinates".
 *
 * @property integer $WeatherAlert_id
 * @property string $latitude
 * @property string $longitude
 *
 * @property WeatherAlert $weatherAlert
 */
class WeatherAlertCoordinates extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'WeatherAlertCoordinates';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['WeatherAlert_id', 'latitude', 'longitude'], 'required'],
            [['WeatherAlert_id'], 'integer'],
            [['latitude', 'longitude'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'WeatherAlert_id' => 'Weather Alert ID',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWeatherAlert()
    {
        return $this->hasOne(WeatherAlert::className(), ['id' => 'WeatherAlert_id']);
    }
}

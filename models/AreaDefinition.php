<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "AreaDefinition".
 *
 * @property integer $id
 * @property integer $WeatherAlertArea_id
 * @property double $latitude
 * @property double $longitude
 * @property double $radius
 *
 * @property WeatherAlertArea $weatherAlertArea
 */
class AreaDefinition extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'AreaDefinition';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['WeatherAlertArea_id', 'latitude', 'longitude'], 'required'],
            [['id', 'WeatherAlertArea_id'], 'integer'],
            [['latitude', 'longitude', 'radius'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'WeatherAlertArea_id' => 'Weather Alert Area ID',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'radius' => 'Radius',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWeatherAlertArea()
    {
        return $this->hasOne(WeatherAlertArea::className(), ['id' => 'WeatherAlertArea_id']);
    }
}

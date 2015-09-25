<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "WeatherAlertArea".
 *
 * @property integer $id
 * @property integer $WeatherAlert_id
 *
 * @property AreaDefinition[] $areaDefinitions
 * @property WeatherAlert $weatherAlert
 */
class WeatherAlertArea extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'WeatherAlertArea';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['WeatherAlert_id'], 'required'],
            [['id', 'WeatherAlert_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'WeatherAlert_id' => 'Weather Alert ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAreaDefinitions()
    {
        return $this->hasMany(AreaDefinition::className(), ['WeatherAlertArea_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWeatherAlert()
    {
        return $this->hasOne(WeatherAlert::className(), ['id' => 'WeatherAlert_id']);
    }
}

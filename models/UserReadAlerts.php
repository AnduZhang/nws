<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "UserReadAlerts".
 *
 * @property integer $User_id
 * @property integer $WeatherAlert_id
 * @property integer $isRead
 *
 * @property User $user
 * @property WeatherAlert $weatherAlert
 */
class UserReadAlerts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'UserReadAlerts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['User_id', 'WeatherAlert_id'], 'required'],
            [['User_id', 'WeatherAlert_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'User_id' => 'User ID',
            'WeatherAlert_id' => 'Weather Alert ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'User_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWeatherAlert()
    {
        return $this->hasOne(WeatherAlert::className(), ['id' => 'WeatherAlert_id'])->from(['WeatherAlertAlias' => WeatherAlert::tableName()]);
    }
}

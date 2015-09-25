<?php

namespace app\models;

use Yii;
use app\helpers\GoogleAPIHelper;

/**
 * This is the model class for table "NRESProperty".
 *
 * @property integer $id
 * @property string $streetAddress
 * @property string $city
 * @property string $state
 * @property string $zipcode
 * @property string $client
 * @property double $latitude
 * @property double $longitude
 * @property integer $status
 */
class NRESProperty extends \yii\db\ActiveRecord
{

    public $addNew;

    const STATUS_ACTIVE = 2;
    const STATUS_INACTIVE = 1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'NRESProperty';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','streetAddress', 'city', 'state', 'zipcode', 'client', 'status'], 'required'],

            [['id', 'status'], 'integer'],
            ['zipcode', 'match', 'pattern' => '^\d{5}(?:[-\s]\d{4})?$^'],
            [['latitude', 'longitude'], 'number'],
            [['state'], 'string','length'=>2],
            ['city', 'match', 'pattern' => '/^[ a-zA-Z_-]+$/'],
            ['status','number','max' => 2, 'min' => 1,'tooBig'=>'123123123'],
            [['streetAddress', 'city', 'zipcode', 'client'], 'string', 'max' => 45],
            [['streetAddress', 'city', 'state', 'zipcode'],'checkGoogleCoordinates','on' => 'create'],
            [['addNew'],'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Property Name',
            'streetAddress' => 'Street Address',
            'city' => 'City',
            'state' => 'State',
            'zipcode' => 'Zipcode',
            'client' => 'Client',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'status' => 'Status',
            'addNew'=>'',
        ];
    }

    public static function alias($type, $code = NULL) {
        $_items = array(
            'status' => array(
                NRESProperty::STATUS_ACTIVE=> Yii::t('user', 'Active'),
                NRESProperty::STATUS_INACTIVE => Yii::t('user', 'Inactive'),
            ),
        );

        if (isset($code))
            return isset($_items[$type][$code]) ? $_items[$type][$code] : false;
        else
            return isset($_items[$type]) ? $_items[$type] : false;
    }


    public static function getStatusAlias() {
        $filter = NRESProperty::alias('status');
        return $filter;
    }

    public function checkGoogleCoordinates() {
        if ($this->streetAddress && $this->city && $this->state && $this->zipcode) {
            $googleHelper = new GoogleAPIHelper();
            $coordinates = $googleHelper->identifyLatLon([$this->streetAddress,$this->city,$this->state,$this->zipcode]);
            if (!$coordinates) {
                $this->addError('latitude', 'Wrong response from Google Maps, please check your address');
                $this->addError('longitude', 'Wrong response from Google Maps, please check your address');
            }
        }
    }
}

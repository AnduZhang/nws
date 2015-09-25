<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class GeoForm extends Model
{
    public $street;
    public $city;
    public $state;
    public $zipcode;
    public $country;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['street','city','state','zipcode','country'], 'required'],
        ];
    }
}
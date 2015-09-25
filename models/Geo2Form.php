<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class Geo2Form extends Model
{
    public $point1;
    public $polygon;
    public $point2;
    public $center;
    public $radius;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
//            [['point1','poly','state','zipcode','country'], 'required'],
        ];
    }
}
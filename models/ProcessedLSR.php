<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ProcessedLSR".
 *
 * @property integer $id
 * @property string $fileName
 * @property string $validationString
 * @property integer $modifiedDate
 */
class ProcessedLSR extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ProcessedLSR';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fileName', 'validationString', 'modifiedDate'], 'required'],
            [['id', 'modifiedDate'], 'integer'],
            [['fileName', 'validationString'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fileName' => 'File Name',
            'validationString' => 'Validation String',
            'modifiedDate' => 'Modified Date',
        ];
    }
}

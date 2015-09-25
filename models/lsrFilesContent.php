<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "lsrFilesContent".
 *
 * @property integer $id
 * @property integer $fileId
 * @property string $fileContent
 * @property integer $time
 *
 * @property LsrFilesStatus $file
 */
class lsrFilesContent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'lsrFilesContent';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fileId', 'time'], 'required'],
            [['fileId', 'time'], 'integer'],
            [['fileContent'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fileId' => 'File ID',
            'fileContent' => 'File Content',
            'time' => 'Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(LsrFilesStatus::className(), ['id' => 'fileId']);
    }
}

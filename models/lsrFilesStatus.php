<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "lsrFilesStatus".
 *
 * @property integer $id
 * @property string $name
 * @property integer $modifiedDate
 *
 * @property LsrFilesContent[] $lsrFilesContents
 */
class lsrFilesStatus extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'lsrFilesStatus';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'modifiedDate'], 'required'],
            [['modifiedDate'], 'integer'],
            [['name'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'modifiedDate' => 'Modified Date',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLsrFilesContents()
    {
        return $this->hasMany(LsrFilesContent::className(), ['fileId' => 'id']);
    }
}

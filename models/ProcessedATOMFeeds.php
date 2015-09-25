<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ProcessedATOMFeeds".
 *
 * @property string $feedURL
 * @property string $updated
 */
class ProcessedATOMFeeds extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ProcessedATOMFeeds';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['feedURL', 'updated'], 'required'],
            [['updated'], 'safe'],
            [['feedURL'], 'string', 'max' => 45]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'feedURL' => 'Feed Url',
            'updated' => 'Updated',
        ];
    }
}

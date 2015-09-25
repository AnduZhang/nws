<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\UserReadAlerts;

/**
 * UserReadAlertsSearch represents the model behind the search form about `app\models\UserReadAlerts`.
 */
class UserReadAlertsSearch extends UserReadAlerts
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['User_id', 'WeatherAlert_id', 'isRead'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = UserReadAlerts::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'User_id' => $this->User_id,
            'WeatherAlert_id' => $this->WeatherAlert_id,
            'isRead' => $this->isRead,
        ]);

        return $dataProvider;
    }
}

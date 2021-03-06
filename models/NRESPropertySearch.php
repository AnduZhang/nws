<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\NRESProperty;

/**
 * NRESPropertySearch represents the model behind the search form about `app\models\NRESProperty`.
 */
class NRESPropertySearch extends NRESProperty
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['streetAddress', 'city', 'state', 'zipcode', 'client','name'], 'safe'],
            [['latitude', 'longitude'], 'number'],
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
        $query = NRESProperty::find();

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
            'id' => $this->id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'streetAddress', $this->streetAddress])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'city', $this->city])
            ->andFilterWhere(['like', 'state', $this->state])
            ->andFilterWhere(['like', 'zipcode', $this->zipcode])
            ->andFilterWhere(['=', 'client', $this->client]);
        if (isset($params['ids'])) {
            $query->andWhere('id IN (' . $params['ids'] . ')');
        }

        if (isset($params['orderBy'])) {

            $query->orderBy([$params['orderBy']=>SORT_ASC]);
        }
        return $dataProvider;
    }
}

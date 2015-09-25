<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\WeatherAlert;

/**
 * WeatherAlertSearch represents the model behind the search form about `app\models\WeatherAlert`.
 */
class WeatherAlertSearch extends WeatherAlert
{

    public $userReadAlerts;
    public $unreadedCount;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'event', 'type','status'], 'integer'],
            [['date', 'severity', 'identifier','type','status','stormName','magnitudeUnit'], 'safe'],
            [['magnitude'], 'number'],
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
        $query = WeatherAlert::find();

        $query->joinWith(['userReadAlerts.weatherAlert']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'magnitude',
                'event',
                'date',
                'severity',
                'stormName' => [
                    'asc' => ['id' => SORT_ASC],
                    'desc' => ['id' => SORT_DESC],
                    'label' => 'Storm Name',
                    'default' => SORT_ASC
                ],
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        $query->andFilterWhere([
            'id' => $this->id,
//            'date' => $this->date,
//            'event' => $this->event,
//            'type' => $this->type,
            'magnitude' => $this->magnitude,
            'identifier' => $this->identifier,
        ]);

        $query->andFilterWhere(['like', 'severity', $this->severity]);
        $query->andFilterWhere(['=', 'WeatherAlert.type', $this->type]);
        $query->andFilterWhere(['=', 'WeatherAlert.event', $this->event]);
        $query->andFilterWhere(['=', 'WeatherAlert.status', WeatherAlert::STATUS_ACTUAL]);
//        var_dump(time() - Yii::$app->params['timePeriodForRecentAlerts']*3600);die;

        $timePeriodForAlert = ($this->type==0)?Yii::$app->params['timePeriodForRecentPreAlerts']:Yii::$app->params['timePeriodForRecentPostAlerts'];
//        if ($this->type == 1) {
//            var_dump($timePeriodForAlert);die;
//        }
        $query->andFilterWhere(['>','WeatherAlert.date',time() - $timePeriodForAlert*3600]);
//        $query->addSelect('COUNT(UserReadAlerts.User_id)');

//        $query->andFilterWhere(['=', 'UserReadAlerts.User_id', Yii::$app->user->id]);
//        $query->andFilterWhere(['=', 'UserReadAlerts.WeatherAlert_id', $this->id]);
//        if (!Yii::$app->request->isPjax) {
//
//        }
        $query->orderBy(['WeatherAlert.date'=>SORT_DESC]);
//        $query->orderBy(['UserReadAlerts.User_id'=>'DESC']);
        $query->groupBy(['WeatherAlert.id']);
        return $dataProvider;
    }
}

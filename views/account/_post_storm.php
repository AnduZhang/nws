<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

?>
<?= GridView::widget([

    'dataProvider' => $dataProviderPost,

    'layout'=>"{items}\n{pager}",
    'tableOptions'=>['class'=>'table table-striped table-hover table-normal table-clickable-rows'],
    'rowOptions' => function ($model, $key, $index, $grid) {
        $className = '';
        if (!$model['userReadAlerts']) {
            $className = "new-alert-post";
        } else {
            if ($model['userReadAlerts']) {
                $className = "new-alert-post";
                foreach ($model['userReadAlerts'] as $userRead) {
                    if ($userRead->User_id == Yii::$app->user->id) {
                        $className = '';
                        break;
                    }
                }
            }
        }
        return ['id' => $model['id'],'onclick' => 'updateStormInformation(this);','class'=>$className];
    },
    'pager' => [
        'firstPageLabel' => 'First',
        'lastPageLabel' => 'Last',
    ],
    'columns' => [
        'id',
        'magnitude',
        [
            'attribute' => 'date',
            'format' => 'text',
            'value' => function($data) {
                return date('m/d/Y H:i',$data->date);
            },
            'label'=>'Date / Time',
        ],
        [
            'attribute' => 'stormName',
            'format'    => 'text',
            'value'     => function ($data) {
                return 'Alert #' . $data->id;
            },
            'label'     => 'Storm Name',
        ],
        [
            'attribute' => 'event',
            'format' => 'text',
            'value' => function($data) {
                switch ($data->event) {
                    case 0:
                        return 'Hurricane';
                        break;
                    case 1:
                        return 'Tornado';
                        break;
                    case 2:
                        return 'Other';
                        break;

                }
            },
            'label'=>'Storm Type',
        ],
    ],
]); ?>
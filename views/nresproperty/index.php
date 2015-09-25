<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\NRESProperty;
use yii\grid\DataColumn;

/* @var $this yii\web\View */
/* @var $searchModel app\models\NRESPropertySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Properties';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="actions">
    <div class="container">
        <?= Html::a(Yii::t('user', 'Add New Properties...'), ['/nresproperty/create'], ['data-method' => 'post','id'=>'addProperty','class'=>'btn btn-blue']) ?>
    </div>
</div>
<div class="container">
    <div class="filters">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
        ]); ?>
        <?php  echo $form->field($searchModel, 'client')->dropDownList(ArrayHelper::map(NRESProperty::find()->orderBy('client ASC')->all(), 'client', 'client')
            ,['class'=>'','onchange'=>'this.form.submit()','prompt'=>'Client: All'])->label(false) ?>

        <?php ActiveForm::end(); ?>
    </div>

    <div class="data-index">
        <?php Pjax::begin(['id' => 'properties']) ?>
        <?= GridView::widget([

            'dataProvider' => $dataProvider,

            'layout'=>"{items}\n{pager}",
            'tableOptions'=>['class'=>'table table-striped table-hover table-normal'],
            'pager' => [
                'firstPageLabel' => 'First',
                'lastPageLabel' => 'Last',
            ],
            'columns' => [

                'client',
                'name',
                'id',

                [
                    'attribute' => 'streetAddress',
                    'format' => 'html',
                    'value' => function($data) {
                            return $data->streetAddress.'<br />'.$data->city.' '.$data->state.' '.$data->zipcode;
                        },
                    'label'=>'Property Address',
                ],
//                'city',
//                'state',
//                'zipcode',
                // 'client',
//                 'latitude',
                [
//                    'class' => DataColumn::className(),
                    'attribute' => 'latitude',
                    'format' => 'html',
                    'value' => function($data) {
                            return $data->latitude.'<br />'.$data->longitude;
                        },
                    'label'=>'Location',
                ],
//                 'longitude',
//                 'status',

                ['class' => 'yii\grid\ActionColumn',
                    'header'=>'Actions',
                    'buttons'=>[
                        'update' => function ($url, $model) {
                                return Html::a('<span class="glyphicon glyphicon-plus"></span>', $url, [
                                    'title' => Yii::t('yii', 'Update Property'),
                                ]);

                            },
                        'delete' => function ($url, $model) {
                                return Html::a('<span class="glyphicon glyphicon-plus"></span>', $url, [
                                    'title' => Yii::t('yii', 'Delete Property'),
                                ]);

                            },
                        'view' => function ($url, $model) {
                                return Html::a('<span class="glyphicon glyphicon-plus"></span>', $url, [
                                    'title' => Yii::t('yii', 'View Property'),
                                ]);

                            }

                    ],
                 'template' => '<span class="view_property">{view}</span> <span class="update_property">{update}</span><span class="delete_property">{delete}</span>',
                    'contentOptions'=>['class'=>'tbl-actions']
                ],
            ],
        ]); ?>
        <?php Pjax::end() ?>
    </div>
</div>
<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CountriesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


?>


<div class="actions">
    <div class="container">
        <a href="modal-add-property-initial.html" class="btn btn-blue">Add New Properties...</a>
    </div>
</div>
<div class="container">
    <div class="data-index">

        <h1><?= Html::encode($this->title) ?></h1>


        <?php Pjax::begin(['id' => 'properties']) ?>
        <?= GridView::widget([
            'dataProvider' => $data,
            'filterModel' => $searchModel,
            'filterPosition'=>'self::FILTER_POS_BODY',
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'name',
                'modifiedDate',
                ['class' => 'yii\grid\ActionColumn'],
            ],
            'tableOptions'=>['class'=>'table table-striped table-hover table-normal']
        ]); ?>
        <?php Pjax::end() ?>
    </div>
</div>


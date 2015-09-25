<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\lsrFilesStatusSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Lsr Files Statuses';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="lsr-files-status-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Lsr Files Status', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'modifiedDate',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>

<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\NRESProperty */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Nresproperties', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="nresproperty-view">


    <?= DetailView::widget([
        'model' => $model,
        'template'=>'<div class="row"><div class="col-sm-4 text-right"><label>{label}</label></div><div class="col-sm-8">{value}</div></div> ',
        'attributes' => [
            'id',
            'streetAddress',
            'city',
            'state',
            'zipcode',
            'client',
            'latitude',
            'longitude',
            [
                'attribute'=>'status',
                'value' => ($model->status==\app\models\NRESProperty::STATUS_ACTIVE)?'Active':'Inactive',
            ],
        ],
    ]) ?>
    <div class="modal-footer">
        <div class="text-center">
            <?= Html::button(Yii::t('user', 'Close'), ['class' => 'btn btn-sm btn-gray', 'tabindex' => '3','onclick'=>'$(\'#modal\').modal(\'hide\')']) ?>
        </div>
    </div>

</div>

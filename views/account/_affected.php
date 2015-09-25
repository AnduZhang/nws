<?php

use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\NRESProperty;


?>
    <div class="filters">
        <?php yii\widgets\Pjax::begin(['id' => 'affected-search','enablePushState'=>FALSE,'clientOptions'=>['type'=>'POST']]) ?>
        <?php $form = ActiveForm::begin([
            'method' => 'POST',
            'id'=>'affected-filter',
            'action'=>['alerts/affectedpropertieslist']
        ]); ?>
        <?php  echo $form->field($searchModel, 'client')->dropDownList(ArrayHelper::map($clients, 'client', 'client')
            ,['class'=>''
            ,'onchange'=>"$.pjax.reload({container:'#affected', timeout: 10000,url: $('#affected-filter').attr('action') + '&' + $('#affected-filter').serialize() + '&id='+gmap.activeId,type:'POST',push:false,replace:false});",'prompt'=>'Client: All'])->label(false) ?>

        <?php ActiveForm::end(); ?>
        <?php yii\widgets\Pjax::end() ?>
    </div>


<?php yii\widgets\Pjax::begin(['id' => 'affected','enablePushState'=>FALSE,'clientOptions'=>['type'=>'POST']]) ?>
    <div class="table-caption"><strong>PROPERTIES AFFECTED </strong> (<?= $dataProvider->totalCount ?>)</div>
<div class="ef-var-h overflow-scroll">
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
        [
            'attribute' => 'latitude',
            'format' => 'html',
            'value' => function($data) {
                return $data->latitude.'<br />'.$data->longitude;
            },
            'label'=>'Location',
        ],
        ],
]); ?>

<?php yii\widgets\Pjax::end() ?>
</div>
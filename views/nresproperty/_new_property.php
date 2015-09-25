<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\NRESProperty */
/* @var $form yii\widgets\ActiveForm */
?>
<br />
<br />
<p class="text-center">
    <?= Html::a(Yii::t('user', 'Add One<br>Property<br><small>(at a time)</small>'), ['/nresproperty/createsingle'],
        ['class'=>'add_single_property btn btn-gray btn-huge mg-right','title'=>'Add New Property']) ?>
    <?= Html::a(Yii::t('user', 'Add Full List of<br>Properties<br><small>(upload a CSV file)</small>'), ['/nresproperty/uploadcsv'],
        ['class'=>'add_single_property btn btn-gray btn-huge mg-right','title'=>'Add Multiple Properties']) ?>
</p>

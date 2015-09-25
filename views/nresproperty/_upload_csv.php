<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\NRESProperty */
/* @var $form yii\widgets\ActiveForm */
?>

<?php $form = ActiveForm::begin([
        'id'                     => 'uploadcsv-form',
//        'type' => 'horizontal',
//        'enableAjaxValidation'   => true,
//        'enableClientValidation' => true,
//        'validateOnBlur'         => false,
        'options'=>['enctype'=>'multipart/form-data','method'=>'POST']
//        'beforeSubmit' => 'alert("2");',

    ]); ?>
<div class="alert alert-info">Select the CSV file with the list of the properties (<a href="sample/file_valid.csv">Download sample</a>) and click on the button <strong>Upload</strong>.</div>

<div class="form-group">
        <?= $form->field($model, 'file')->fileInput() ?>
        <div class="small-ajax-loader"></div>
    </div>
<sup class="req">*</sup>  Required fields.
    <div class="form-group">
        <div class="modal-footer">
            <div class="text-center">
                <?= Html::button(Yii::t('user', 'Cancel'), ['class' => 'btn btn-sm btn-link', 'tabindex' => '3','onclick'=>'$(\'#modal\').modal(\'hide\')']) ?>
                <?= Html::submitButton('Upload', ['class' => 'btn btn-sm btn-blue','id'=>'property-save-add-another']) ?>


            </div>
        </div>

    </div>

    <?php ActiveForm::end(); ?>

<div id="uploadLog"class="alert alert-danger" style="display: none;"></div>



<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
echo 'Please enter URL from <a href="http://alerts.weather.gov/">alerts.weather.gov</a> website.';
echo '<hr />';
$form = ActiveForm::begin(['id' => 'cap-form']); ?>
<?= $form->field($model, 'url') ?>

<div class="form-group">
    <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
</div>
<?php ActiveForm::end();

if ($requestData) {
    echo 'CSV Data: <br />';
    echo '<pre>';
    print_r($requestData);
}

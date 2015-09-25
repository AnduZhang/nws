<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
echo 'Geolocation test page.';
echo '<hr />';
$form = ActiveForm::begin(['id' => 'cap-form']); ?>
<?= $form->field($model, 'street') ?>
<?= $form->field($model, 'city') ?>
<?= $form->field($model, 'state') ?>
<?= $form->field($model, 'zipcode') ?>
<?= $form->field($model, 'country') ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
    </div>
<?php ActiveForm::end();

if ($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

?>

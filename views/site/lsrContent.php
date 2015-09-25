<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
echo 'Please enter filename (example - sn.0000.txt)';
echo '<hr />';
$form = ActiveForm::begin(['id' => 'cap-form']); ?>
<?= $form->field($model, 'name') ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
    </div>
<?php ActiveForm::end();

if ($lsrFileContent) {
    echo 'LSR Data: <br />';
    echo 'Scanned: '.date('Y-m-d H:i:s',$lsrFileContent->time).'<br />';
    echo '<pre>';
    print_r(json_decode($lsrFileContent->fileContent));
    echo '</pre>';
} else {
    echo '<br /><strong>File is not exists.</strong>';
}

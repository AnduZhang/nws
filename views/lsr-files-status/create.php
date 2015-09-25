<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\lsrFilesStatus */

$this->title = 'Create Lsr Files Status';
$this->params['breadcrumbs'][] = ['label' => 'Lsr Files Statuses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="lsr-files-status-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

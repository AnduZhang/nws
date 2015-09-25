<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\NRESProperty */

$this->title = 'Create Nresproperty';
$this->params['breadcrumbs'][] = ['label' => 'Nresproperties', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="nresproperty-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
/* @var $this yii\web\View */
if ($performLogin) {
    $this->registerJs('jQuery("#loginButton").click()');
}

?>

<h1>Welcome</h1>

<p>Project homepage here</p>

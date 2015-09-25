<?php

Yii::setAlias('@nresTheme', '@web/themes/nres/');
Yii::setAlias('@nresThemeAssets', '@web/themes/nres/assets/');
$themePath = '/themes/nres/';

use app\assets\NresAsset;
use yii\bootstrap\Modal;
NresAsset::register($this);

$currentUrl = \Yii::$app->request->getPathInfo();
?>
<?php $this->beginPage(); ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <?= $this->render('head') ?>
    </head>
<?php $this->beginBody(); ?>
<div id="body-inner">

    <?= $this->render('header') ?>
    <div class="behind-header"></div>
    <main>
        <div class="row">
            <div class="col-xs-12" id="flash-message-container">
                <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
                    <?php if (in_array($type, ['success', 'danger', 'warning', 'info'])): ?>

                        <div class="alert alert-<?= $type ?>">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <?= $message ?>
                        </div>
                    <?php endif ?>
                <?php endforeach ?>
            </div>
        </div>
        <?php echo $content ?>
    </main>
</div>
<?php
Modal::begin([
    'id' => 'modal',
    'header'=>'<h4 class="modal-title"></h4>',
    'closeButton'=>['tag'=>'a','label'=>'<img src="'.Yii::getAlias('@nresThemeAssets').'img/modal-close.png" />']
]);

echo "<div id='modalContent'></div>";

Modal::end();
?>

<?php $this->endBody(); ?>
<?php $this->endPage(); ?>
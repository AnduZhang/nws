<?php
use yii\helpers\Html;
use yii\widgets\Menu;
use yii\helpers\Url;
use yii\helpers\BaseUrl;

$themePath = '/themes/nres/assets/';
?>
<header class="hdr-fixed hdr-dark">
    <div class="strip"></div>
    <div class="hdr-main">
        <div class="hdr-main-inner">
            <div class="hdr-a">
                <a href="<?= BaseUrl::base(true) ?>" class="logo"><img src="<?= Yii::getAlias('@nresThemeAssets') ?>img/logo.png" alt="WeatherSmart"></a>
            </div>
            <?php
            if (!\Yii::$app->user->isGuest) { ?>
                <?php echo Menu::widget(array(
                    'options' => array('class' => 'hdr-b'),
                    'items' => array(
                        array('label' => 'Alerts', 'url' => array('/account/alerts'),'options'=> ['class'=>'btn btn-black']),
                        array('label' => 'Properties', 'url' => array('/nresproperty/index'),'options'=> ['class'=>'btn btn-black'])
                    ),
                )); ?>
            <?php } ?>

            <div class="hdr-c">
                <ul>
                    <li><span class="date"><?= date('d F, Y') ?></span></li>
                    <li><?= Html::a(Yii::t('user', 'Help'), ['/site/help'],['class'=>'help']) ?></li>
                    <?php if (\Yii::$app->user->isGuest) { ?>
                        <li>
                        <?= Html::a(Yii::t('user', 'Register'), ['/user/registration/register'], ['data-method' => 'post','id'=>'registerButton']) ?>
                        </li>
                    <?php } ?>
                    <li>

                        <?php if (\Yii::$app->user->isGuest) { ?>
                            <?= Html::a(Yii::t('user', 'Sign in'), ['/user/security/login'], ['data-method' => 'post','id'=>'loginButton']) ?>
                        <?php } else { ?>
                            <a href="#" class="dropdown-toggle hdr-account-name" data-toggle="dropdown" title="<?php echo \Yii::$app->user->identity->firstName.' '.\Yii::$app->user->identity->lastName ?>"><span>
                                    <?php echo \Yii::$app->user->identity->firstName.' '.\Yii::$app->user->identity->lastName ?>
                                </span> <i class="caret sharp lg"></i></a>
                            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                <li><?= Html::a(Yii::t('user', 'Account Settings'), ['/user/settings/account'], ['data-method' => 'post','id'=>'accountSettings']) ?></li>
                                <li><?= Html::a(Yii::t('user', 'Sign out'), ['/user/security/logout'], ['data-method' => 'post']) ?></li>
                            </ul>
                        <?php } ?>
                    </li>
                </ul>

            </div>
        </div>
    </div>
</header>
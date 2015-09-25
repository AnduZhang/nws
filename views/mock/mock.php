<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->registerJsFile( 'https://maps.googleapis.com/maps/api/js?v=3.exp',["position"=>\yii\web\View::POS_HEAD]);
$this->registerCss('
        #map-canvas {
             width: 100%;
             height: 400px;
        }
         ');
/**
 * @var yii\web\View $this
 * @var dektrium\user\models\User $user
 * @var dektrium\user\Module $module
 */


?>
<script type="text/javascript">
    function initialize() {
        var mapOptions = {
            center: { lat: 43.00464, lng: -105.33},
            zoom: 8
        };
        var map = new google.maps.Map(document.getElementById('map-canvas'),
            mapOptions);
        google.maps.event.addListener(map, "click", function (event) {
            var latitude = event.latLng.lat();
            var longitude = event.latLng.lng();
            console.log( latitude + ', ' + longitude );
            $('#mockform-startcoordinates').val(latitude + ', ' + longitude);
        }); //end addListener
    }
    google.maps.event.addDomListener(window, 'load', initialize);
    //Add listener

</script>
<br />

<div class="container">
    <h1>Mock data Tool</h1>
<?php $form = ActiveForm::begin([
    'id'                     => 'mock-form',
    'enableAjaxValidation'   => true,
//    'enableClientValidation' => true
]); ?>
    <span>Select a start point for generating coordinates</span>
    <div id="map-canvas"></div>
    <div class="form-group">
        <?= $form->field($model, 'startCoordinates')->textInput()  ?>
    </div>
<div class="form-group">
    <?= $form->field($model, 'countPreStorm')->dropDownList([0,1,2,3,4,5,6,7,8,9]) ?>
</div>
<div class="form-group">
    <?= $form->field($model, 'countPostStorm')->dropDownList([0,1,2,3,4,5,6,7,8,9]) ?>
</div>
    <div class="form-group">
        <?= $form->field($model, 'countPreAffected')->dropDownList([0,1,2,3,4,5,6,7,8,9]) ?>
    </div>
<div class="form-group">
    <?= $form->field($model, 'countPostAffected')->dropDownList([0,1,2,3,4,5,6,7,8,9]) ?>
</div>

<div class="form-group">
    <?= $form->field($model, 'countStormPath')->dropDownList([0,1,2,3,4,5,6,7,8,9]) ?>
</div>
<div class="form-group">
    <?= $form->field($model, 'countAffectedInStormPath')->dropDownList([0,1,2,3,4,5,6,7,8,9]) ?>
</div>
<div class="form-group">
    <?= $form->field($model, 'cleanDb')->checkbox() ?>
</div>

<div class="modal-footer">
    <div class="text-center">
        <?= Html::submitButton(Yii::t('user', 'Continue'), ['class' => 'btn btn-sm btn-blue']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
</div>

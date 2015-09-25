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
<style>
    #map-canvas {
        height: 400px;
        width: 100%;
        margin: 0px;
        padding: 0px
    }
</style>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>

<script>
    // This example displays a marker at the center of Australia.
    // When the user clicks the marker, an info window opens.

    function initialize() {

        var bounds = new google.maps.LatLngBounds();
        var CoordinatesSummLat = 0;
        var CoordinatesSummLng = 0;
        var count = 0;

        <?php if (isset($data['latLon']['google']) && $data['latLon']['google']) { ?>
            count++;
            CoordinatesSummLat+= <?php echo $data['latLon']['google']->lat ?>;
            CoordinatesSummLng+= <?php echo $data['latLon']['google']->lng ?>;
            bounds.extend(new google.maps.LatLng(<?php echo $data['latLon']['google']->lat ?>, <?php echo $data['latLon']['google']->lng ?>));
        <?php } ?>

        <?php if (isset($data['latLon']['mapQuest']) && $data['latLon']['mapQuest']) { ?>
            count++;
            CoordinatesSummLat+= <?php echo $data['latLon']['mapQuest']->lat ?>;
            CoordinatesSummLng+= <?php echo $data['latLon']['mapQuest']->lng ?>;
            bounds.extend(new google.maps.LatLng(<?php echo $data['latLon']['mapQuest']->lat ?>, <?php echo $data['latLon']['mapQuest']->lng ?>));
        <?php } ?>

        <?php if (isset($data['latLon']['bingMap']) && $data['latLon']['bingMap']) { ?>
            count++;
            CoordinatesSummLat+= <?php echo $data['latLon']['bingMap'][0] ?>;
            CoordinatesSummLng+= <?php echo $data['latLon']['bingMap'][1] ?>;
            bounds.extend(new google.maps.LatLng(<?php echo $data['latLon']['bingMap'][0] ?>, <?php echo $data['latLon']['bingMap'][1] ?>));
        <?php } ?>
        var mapOptions = {
            zoom: 4,
            center: new google.maps.LatLng((CoordinatesSummLat / count), (CoordinatesSummLng / count)),
            mapTypeId: google.maps.MapTypeId.TERRAIN
        }
//        var myLatlng = new google.maps.LatLng(-28.363882,131.044922);
//        var mapOptions = {
//            zoom: 4,
//            center: myLatlng
//        };

        var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
        <?php if (isset($data['latLon']['google']) && $data['latLon']['google']) { ?>
        var googleLocation = new google.maps.LatLng(<?php echo $data['latLon']['google']->lat ?>,<?php echo $data['latLon']['google']->lng ?>);
        var contentString = 'Google maps - (<?php echo $data['latLon']['google']->lat ?>,<?php echo $data['latLon']['google']->lng ?>)';
        var infowindow = new google.maps.InfoWindow({
            content: contentString
        });


        var googleMarker = new google.maps.Marker({
            position: googleLocation,
            map: map,
            title: ''
        });

        google.maps.event.addListener(googleMarker, 'click', function() {
            infowindow.open(map,googleMarker);
        });

        <?php } ?>

        <?php if (isset($data['latLon']['mapQuest']) && $data['latLon']['mapQuest']) { ?>
        var mapQuestLocation = new google.maps.LatLng(<?php echo $data['latLon']['mapQuest']->lat ?>,<?php echo $data['latLon']['mapQuest']->lng ?>);
        var contentString = 'MapQuest - (<?php echo $data['latLon']['mapQuest']->lat ?>,<?php echo $data['latLon']['mapQuest']->lng ?>)';
        var infowindow2 = new google.maps.InfoWindow({
            content: contentString
        });


        var mapQuestMarker = new google.maps.Marker({
            position: mapQuestLocation,
            map: map,
            title: ''
        });

        google.maps.event.addListener(mapQuestMarker, 'click', function() {
            infowindow2.open(map,mapQuestMarker);
        });

        <?php } ?>

        <?php if (isset($data['latLon']['bingMap']) && $data['latLon']['bingMap']) { ?>
        var bingMapLocation = new google.maps.LatLng(<?php echo $data['latLon']['bingMap'][0] ?>,<?php echo $data['latLon']['bingMap'][1] ?>);
        var contentString = 'BingMap - (<?php echo $data['latLon']['bingMap'][0] ?>,<?php echo $data['latLon']['bingMap'][1] ?>)';
        var infowindow3 = new google.maps.InfoWindow({
            content: contentString
        });


        var bingMapMarker = new google.maps.Marker({
            position: bingMapLocation,
            map: map,
            title: ''
        });

        google.maps.event.addListener(bingMapMarker, 'click', function() {
            infowindow3.open(map,bingMapMarker);
        });

        <?php } ?>

        map.fitBounds(bounds);

//        var marker2 = new google.maps.Marker({
//            position: myLatlng2,
//            map: map,
//            title: 'Uluru (Ayers Rock)'
//        });

    }

    google.maps.event.addDomListener(window, 'load', initialize);

</script>

<div id="map-canvas"></div>
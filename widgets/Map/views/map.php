<?php
$JsonObject = json_decode(stripcslashes($json));
if (!$JsonObject) {
    echo 'Invalid input JSON';
    return false;
}
//var_dump(json_decode(stripcslashes($json)));die;
$this->registerJsFile( 'https://maps.googleapis.com/maps/api/js?v=3.exp',["position"=>\yii\web\View::POS_HEAD]);
    $this->registerCss('
        #map-canvas {
             width: '.$JsonObject->mapConfig->width.'px;
             height: '.$JsonObject->mapConfig->height.'px;
         }

         #storm-info {
             width: '.$JsonObject->mapConfig->width.'px;
             text-align: center;
            border: 1px solid #ccc;
            padding: 15px;
            font-size: 16px;
            font-weight: bold;
         }

         ');
?>

<?php
//$json = $this->EscapeAposAndQuotes($json);
//var_dump($json);die;
$script = <<< JS

        var jsonText = "$json";
        var mapJson = JSON.parse(jsonText);

        var allStormMarkers = [];
        function initialize() {
            //Calculation map center
            var bounds = new google.maps.LatLngBounds();

            var CoordinatesSummLat = 0;
            var CoordinatesSummLng = 0;

            var stromCoordinates = [];
//            console.log(mapJson.storm.length);
            jQuery.each(mapJson.storm,function(i,e) {
                CoordinatesSummLat+= e.path.Lat;
                CoordinatesSummLng+= e.path.Lng;
                stromCoordinates.push(new google.maps.LatLng(e.path.Lat, e.path.Lng));
                bounds.extend(new google.maps.LatLng(e.path.Lat, e.path.Lng));
            });

            var propertyCoordinates = [];

            jQuery.each(mapJson.property,function(i,e) {
                CoordinatesSummLat+= e.path.Lat;
                CoordinatesSummLng+= e.path.Lng;
                propertyCoordinates.push(new google.maps.LatLng(e.path.Lat, e.path.Lng));
                bounds.extend(new google.maps.LatLng(e.path.Lat, e.path.Lng));
            });

            var mapOptions = {
                zoom: 6,
                center: new google.maps.LatLng((CoordinatesSummLat/mapJson.storm.length), (CoordinatesSummLng/mapJson.storm.length)),
                mapTypeId: google.maps.MapTypeId.TERRAIN
            };

            var map = new google.maps.Map(document.getElementById('map-canvas'),
                mapOptions);

            var stormPath = new google.maps.Polyline({
                path: stromCoordinates,
                //geodesic: true,
                strokeColor: '#908f8d',
                strokeOpacity: 1.0,
                strokeWeight: 2,
                zIndex : 1
            });

            stormPath.setMap(map);

            jQuery.each(stromCoordinates,function(i,e) {
                marker = new google.maps.Marker({
                    position: e,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        fillOpacity: 1.0,
                        fillColor: '#a9a9a9',
                        strokeOpacity: 1.0,
                        strokeColor: '#939393',
                        strokeWeight: 2.0,
                        scale: 8
                    },
//                draggable: true,
                    map: map,
                    title: i.toString()
                });
                allStormMarkers.push(marker);
                markerInit(marker);

            });

            jQuery.each(propertyCoordinates,function(i,e) {
                var image = mapJson.mapConfig.stormIconPath;
                var beachMarker = new google.maps.Marker({
                    position: e,
                    map: map,
                    icon: image
                });
            });

            map.fitBounds(bounds);

            google.maps.event.addListener(marker, 'click', function(e) {

            });
        }

        function markerInit(marker) {

              google.maps.event.addListener(marker, 'click', function() {
                  jQuery.each(allStormMarkers,function(i,e) {
                    e.setIcon({
                        path: google.maps.SymbolPath.CIRCLE,
                        fillOpacity: 1.0,
                        fillColor: '#a9a9a9',
                        strokeOpacity: 1.0,
                        strokeColor: '#939393',
                        strokeWeight: 2.0,
                        scale: 8
                    });
                  });
                    marker.setIcon({
                        path: google.maps.SymbolPath.CIRCLE,
                        fillOpacity: 1.0,
                        fillColor: '#ff9130',
                        strokeOpacity: 1.0,
                        strokeColor: '#fff',
                        strokeWeight: 2.0,
                        scale: 12
                    });
                    jQuery('#storm-info').html('Information about selected storm. Title - ' + marker.getTitle());
              });
        }



        google.maps.event.addDomListener(window, 'load', initialize);

JS;
$this->registerJs($script, \yii\web\View::POS_HEAD);
?>
<div id="storm-info">

</div>
<div id="map-canvas"></div>

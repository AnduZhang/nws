<?php
$this->registerJsFile( 'https://maps.googleapis.com/maps/api/js?v=3.exp',["position"=>\yii\web\View::POS_HEAD]);
//$this->registerCss('
//#map-canvas {
//width: 800px;
//height: 600px;
//}
//');
?>

<div class="expand-fully vertical nudge-right">
    <div class="choose-overlay" style=""></div>
    <div class="actions text-center">
        <div class="btn-group btn-group-sm">
            <a href="#" class="btn btn-geyser on" id="map_view">MAP VIEW</a>
            <a href="#" class="btn btn-geyser" id="list_view">LIST VIEW</a>
        </div>
    </div>
<!--    <div id="alerts-map-view">-->
    <div class="ef-fix-h">
        <div class="storm-properties">
            <div class="sp-a">
                <div class="text-muted">Storm Name:</div>
                <div class="sp-name" title="Storm Name Goes Here"></div>
            </div>
            <div class="sp-b">
                <div class="ico-storm"></div>
            </div>
            <div class="sp-c">
                <span class="text-muted">Magnitude: <span class="pr-value"></span></span> <br>
                <span class="text-muted">Location: <span class="pr-value"></span></span> <br>
            </div>
        </div>
    </div>
    <div class="ef-var-h storm-map">
        <div id="map-canvas" style="width: 100%;height: 100%;"></div>
        <div id="affected-list" style="display: none"></div>
    </div>
    <div class="ef-fix-h">
        <div class="actions text-center">
            <a href="#" class="btn btn-blue btn-sm" id="csv_export">EXPORT TO SPREADSHEET</a>

            <a href="#" class="btn btn-blue btn-sm" id="pdf_export">SAVE AS PDF</a>
        </div>
    </div>
</div>
<!--</div>-->


<?php
//$json = $this->EscapeAposAndQuotes($json);
//var_dump($json);die;
$script = <<< JS
        gmap = [] || gmap;
        gmap.activeId = 0;
        gmap.currentView = 0;
        gmap.currentAffectedFilter = null;
        gmap.activeTab = 0;

        gmap.json = null;

        gmap.initialize = function() {
            if (!gmap.json) {
            //Load sample map
              var mapOptions = {
                zoom: 8,
                center: new google.maps.LatLng(-34.397, 150.644)
              };
              map = new google.maps.Map(document.getElementById('map-canvas'),
                  mapOptions);

            } else {
                gmap.drawAlertPolygon();
            }
            return true;
        };

        gmap.drawAlertPolygon = function() {

            var radiusExists = false;
            var circleCoordinates = [];
            var PolygonCoordinates = [];
            var bounds = new google.maps.LatLngBounds();
            var CoordinatesSummLat = 0;
            var CoordinatesSummLng = 0;

            if (gmap.json.stormPath) {
                gmap.drawStormPath();
                return false;
            }
            $.each(gmap.json.coordinates,function(i,c) {

                if (c.radius) {
                    radiusExists = true;
                    circleCoordinates.push(c);
                // Add the circle for this city to the map.
                } else {

                    PolygonCoordinates.push(new google.maps.LatLng(c.latitude, c.longitude));

                }
                    CoordinatesSummLat+= c.latitude;
                    CoordinatesSummLng+= c.longitude;
                    bounds.extend(new google.maps.LatLng(c.latitude, c.longitude));
            });
            var mapOptions = {
                zoom: 6,
                center: new google.maps.LatLng((CoordinatesSummLat/gmap.json.coordinates.length), (CoordinatesSummLng/gmap.json.coordinates.length)),
                mapTypeId: google.maps.MapTypeId.TERRAIN
            };

            var map = new google.maps.Map(document.getElementById('map-canvas'),
                mapOptions);
            bermudaTriangle = new google.maps.Polygon({
                paths: PolygonCoordinates,
                strokeColor: '#FF0000',
                strokeOpacity: 0.8,
                strokeWeight: 3,
                fillColor: '#FF0000',
                fillOpacity: 0.35
            });

            jQuery.each(gmap.json.affected,function(i,e) {
            console.log(e);
                var image = 'images/marker.png';
                var propertyMarker = new google.maps.Marker({
                    position: new google.maps.LatLng(e.latitude, e.longitude),
                    map: map,
                    icon: image,
                    id:e.id
                });
                var infowindow = new google.maps.InfoWindow({
                      content: gmap.GetInfoWindowHtml(e)
                 });
                   google.maps.event.addListener(propertyMarker, 'mouseover', function() {
                    infowindow.open(map,propertyMarker);
                  });
                   google.maps.event.addListener(propertyMarker, 'mouseout', function() {
                    infowindow.close(map,propertyMarker);
                  });
            });

            bermudaTriangle.setMap(map);

            var c = circleCoordinates[0];

            if (radiusExists) {
                var circleOptions = {
                      strokeColor: '#FF0000',
                      strokeOpacity: 0.8,
                      strokeWeight: 2,
                      fillColor: '#FF0000',
                      fillOpacity: 0.35,
                      map: map,
                      center: new google.maps.LatLng(c.latitude, c.longitude),
                      radius: 1000*c.radius
                    };

                    alertCircle = new google.maps.Circle(circleOptions);
                    map.fitBounds(alertCircle.getBounds());
            } else {
                map.fitBounds(bounds);
            }


              //var marker2 = new google.maps.Marker({
              //    position: bounds.getCenter(),
              //    map: map
              //});

            google.maps.event.addListener(map, "rightclick", function(event) {
                var lat = event.latLng.lat();
                var lng = event.latLng.lng();
                // populate yor box/field with lat, lng
                alert("Lat=" + lat + "; Lng=" + lng);
            });
        };
        gmap.GetInfoWindowHtml = function(e) {

        return '<strong>Property Name: </strong>' + e.name + '<br />'
         + '<strong>Address: </strong>' + e.streetAddress + '<br />' + e.city + ' ' + e.state + ' ' + e.zipcode+ '<br />' +
         '<strong>Location: </strong>' + e.latitude + ', ' + e.longitude + '<br />' +
         '<strong>Client: </strong>' + e.client;

        }
        gmap.drawStormPath = function() {
            var radiusExists = false;
            var circleCoordinates = [];
            var PolygonCoordinates = [];
            var bounds = new google.maps.LatLngBounds();


            $.each(gmap.json.coordinates,function(i,c) {
                if (c.radius) {
                    radiusExists = true;
                } else {
                    PolygonCoordinates.push(new google.maps.LatLng(c.latitude, c.longitude));
                }
                    bounds.extend(new google.maps.LatLng(c.latitude, c.longitude));
            });



            var stormPathCoordinatesCenter = [];
            stormPathCoordinatesCenter.push(bounds.getCenter());


            $.each(gmap.json.stormPath, function(i,a) {
                //we need to reset all counters
                circleCoordinates = [];
                bounds = new google.maps.LatLngBounds();
                //CoordinatesSummLat = 0;
                //CoordinatesSummLng = 0;
                if (typeof(a.radius)=='undefined') {
                    $.each(a, function(i,coord) {
                        bounds.extend(new google.maps.LatLng(coord.centerLat, coord.centerLon));
                    });
                } else {
                bounds.extend(new google.maps.LatLng(a.centerLat, a.centerLon));
                }

                //alert(bounds.getCenter());
                stormPathCoordinatesCenter.push(bounds.getCenter());
            });

            var mapOptions = {
                zoom: 6,
                mapTypeId: google.maps.MapTypeId.TERRAIN
            };

            var map = new google.maps.Map(document.getElementById('map-canvas'),
                mapOptions);
            if (radiusExists) {
                 var circleOptions = {
                      strokeColor: '#FF0000',
                      strokeOpacity: 0.8,
                      strokeWeight: 2,
                      fillColor: '#FF0000',
                      fillOpacity: 0.35,
                      map: map,
                      center: stormPathCoordinatesCenter[0],
                      radius: 1000*gmap.json.coordinates[0].radius
                    };

                    alertCircle = new google.maps.Circle(circleOptions);
            } else {
                bermudaTriangle = new google.maps.Polygon({
                    paths: PolygonCoordinates,
                    strokeColor: '#FF0000',
                    strokeOpacity: 0.8,
                    strokeWeight: 3,
                    fillColor: '#FF0000',
                    fillOpacity: 0.35
                });

                bermudaTriangle.setMap(map);
            }




            bounds = new google.maps.LatLngBounds();
            $.each(stormPathCoordinatesCenter, function(i,sp) {
                bounds.extend(sp);
                paramfillColor = '#a9a9a9';
                if (i==0) {
                    paramfillColor = '#ff9130';
                }


                marker = new google.maps.Marker({
                    position: sp,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        fillOpacity: 1.0,
                        fillColor: paramfillColor,
                        strokeOpacity: 1.0,
                        strokeColor: '#939393',
                        strokeWeight: 2.0,
                        scale: 8
                    },
//                draggable: true,
                    map: map,
                    title: i.toString()
                });
                //allStormMarkers.push(marker);
                //markerInit(marker);


            });

            var stormPathLine = new google.maps.Polyline({
                path: stormPathCoordinatesCenter,
                //geodesic: true,
                strokeColor: '#908f8d',
                strokeOpacity: 1.0,
                strokeWeight: 2,
                zIndex : 1
            });

            stormPathLine.setMap(map);
            jQuery.each(gmap.json.affected,function(i,e) {
                var image = 'images/marker.png';
                var propertyMarker = new google.maps.Marker({
                    position: new google.maps.LatLng(e.latitude, e.longitude),
                    map: map,
                    icon: image,
                    id:e.id


                });

                var infowindow = new google.maps.InfoWindow({
                      content: gmap.GetInfoWindowHtml(e)
                 });
                   google.maps.event.addListener(propertyMarker, 'mouseover', function() {
                    infowindow.open(map,propertyMarker);
                  });
                   google.maps.event.addListener(propertyMarker, 'mouseout', function() {
                    infowindow.close(map,propertyMarker);
                  });
            });

            map.fitBounds(bounds);
        };


        google.maps.event.addDomListener(window, 'load', gmap.initialize);



JS;
$this->registerJs($script, \yii\web\View::POS_HEAD);
?>
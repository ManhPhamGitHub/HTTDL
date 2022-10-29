<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>OpenStreetMap &amp; OpenLayers - Marker Example</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        
        <link rel="stylesheet" href="https://openlayers.org/en/v4.6.5/css/ol.css" type="text/css" />
        <script src="https://openlayers.org/en/v4.6.5/build/ol.js" type="text/javascript"></script>
            
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js" type="text/javascript"></script>
       
        <style>
            /*
            .map, .righ-panel {
                height: 500px;
                width: 80%;
                float: left;
            }
            */
            .map, .righ-panel {
                height: 98vh;
                width: 60vw;
                float: left;
            }
            .map {
                border: 1px solid #000;
            }
        </style>
    </head>
    <body onload="initialize_map();">
            <div>
                <h1>BẢN ĐỒ CÔNG VIÊN HÀ NỘI</h1>
                    <div id="map" class="map"></div>
                    <!--<div id="map" style="width: 80vw; height: 100vh;"></div>-->
                    <div>
                            <span>Search</span>
                            <form method="POST">
                                <input type="text" name="search">
                                <input type="submit" name="submit" value="Tìm" >  
                            </form>
                        </div>
                    <div id="info"></div>
            </div>
        <?php include 'CMR_pgsqlAPI.php' ?>
        <?php
        if (isset($_POST['submit'])){
            $search = $_POST['search'];
            if ($search != ""){
                getSearch($search);  
                $_POST = array();              
            }
        }
        ?>
            
            <script>
        //$("#document").ready(function () {
            var format = 'image/png';
            var map;
            var minX = 105.281219482422;
            var minY = 20.5604095458984;
            var maxX = 106.023750305176;
            var maxY = 21.38938331604;
            var cenX = (minX + maxX) / 2;
            var cenY = (minY + maxY) / 2;
            var mapLat = cenY;
            var mapLng = cenX;
            var mapDefaultZoom = 10;
            function initialize_map() {
                //*
                layerBG = new ol.layer.Tile({
                    source: new ol.source.OSM({})
                });
                //*/
                var layerCMR_adm1 = new ol.layer.Image({
                    source: new ol.source.ImageWMS({
                        ratio: 1,
                        url: 'http://localhost:8888/geoserver/example/wms?',
                        params: {
                            'FORMAT': format,
                            'VERSION': '1.1.1',
                            STYLES: '',
                            LAYERS: 'example:khuvuchn',
                        }
                    })
                });
                var layerCMR_adm2 = new ol.layer.Image({
                    source: new ol.source.ImageWMS({
                        ratio: 1,
                        url: 'http://localhost:8888/geoserver/example/wms?',
                        params: {
                            'FORMAT': format,
                            'VERSION': '1.1.1',
                            STYLES: '',
                            LAYERS: 'example:hnpark',
                        }
                    })
                });
                var viewMap = new ol.View({
                    center: ol.proj.fromLonLat([mapLng, mapLat]),
                    zoom: mapDefaultZoom
                    //projection: projection
                });
                map = new ol.Map({
                    target: "map",
                    layers: [layerBG, layerCMR_adm1,layerCMR_adm2],
                    //layers: [layerCMR_adm1],
                    view: viewMap
                });
                //map.getView().fit(bounds, map.getSize());
                
                var styles = {
                    'MultiPolygon': new ol.style.Style({
                        stroke: new ol.style.Stroke({
                            color: 'yellow', 
                            width: 2
                        })
                    })
                };
                var styleFunction = function (feature) {
                    return styles[feature.getGeometry().getType()];
                };
                var vectorLayer = new ol.layer.Vector({
                    //source: vectorSource,
                    style: styleFunction
                });
                map.addLayer(vectorLayer);

                function createJsonObj(result) {                    
                    var geojsonObject = '{'
                            + '"type": "FeatureCollection",'
                            + '"crs": {'
                                + '"type": "name",'
                                + '"properties": {'
                                    + '"name": "EPSG:4326"'
                                + '}'
                            + '},'
                            + '"features": [{'
                                + '"type": "Feature",'
                                + '"geometry": ' + result
                            + '}]'
                        + '}';
                    return geojsonObject;
                }
                function drawGeoJsonObj(paObjJson) {
                    var vectorSource = new ol.source.Vector({
                        features: (new ol.format.GeoJSON()).readFeatures(paObjJson, {
                            dataProjection: 'EPSG:4326',
                            featureProjection: 'EPSG:3857'
                        })
                    });
                    var vectorLayer = new ol.layer.Vector({
                        source: vectorSource
                    });
                    map.addLayer(vectorLayer);
                }
                function displayObjInfo(result, coordinate)
                {
					$("#info").html(result);
                }
                
                map.on('singleclick', function (evt) {
                    //alert("coordinate org: " + evt.coordinate);
                    //var myPoint = 'POINT(12,5)';
                    var lonlat = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326');
                    var lon = lonlat[0];
                    var lat = lonlat[1];
                    var myPoint = 'POINT(' + lon + ' ' + lat + ')';
                    //alert("myPoint: " + myPoint);
                    //*
                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        //dataType: 'json',
                        //data: {functionname: 'reponseGeoToAjax', paPoint: myPoint},
                        data: {functionname: 'getInfoCMRToAjax', paPoint: myPoint},
                        success : function (result, status, erro) {
                            console.log("result",result);
                            displayObjInfo(result, evt.coordinate );
                        },
                        error: function (req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                    //*/
                });
            };
        //});
        function TEST(data){
            displayObjInfo(data, evt.coordinate );

                }
        </script>
    </body>
</html>
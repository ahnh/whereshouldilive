<?php
ini_set ( 'auto_detect_line_endings', TRUE );
$file = fopen ( "http://www.cbc.ca/toronto/features/crimemap/spreadsheets/assault.csv", "r" );
//$file = fopen ( "http://www.cbc.ca/toronto/features/crimemap/spreadsheets/sexualassault.csv", "r" );
//$file = fopen ( "http://www.cbc.ca/toronto/features/crimemap/spreadsheets/breakandenter.csv", "r" );
//$file = fopen ( "http://www.cbc.ca/toronto/features/crimemap/spreadsheets/robbery.csv", "r" );
//$file = fopen ( "http://www.cbc.ca/toronto/features/crimemap/spreadsheets/drug-charges.csv", "r" );
//$file = fopen ( "http://www.cbc.ca/toronto/features/crimemap/spreadsheets/stolen-cars.csv", "r" );
//$file = fopen ( "http://www.cbc.ca/toronto/features/crimemap/spreadsheets/theft.csv", "r" );
//$file = fopen ( "http://www.cbc.ca/toronto/features/crimemap/spreadsheets/murder.csv", "r" );

$data = array ();
$i = 0;
while ( ($row = fgetcsv ( $file )) !== FALSE ) {
	$data [$i] = $row;
	$i++;
}
fclose ( $file );

ini_set ( 'auto_detect_line_endings', FALSE );
$conn = mysqli_connect ( "localhost", "root", "", "db190263_should" );
for($x = 1; $x < count ( $data ); $x ++) {
	if ($data[$x][1] && $data[$x][1] != "")
	{
		$query = "INSERT INTO db190263_should.area_type (name) VALUES(\"" . $data[$x][1]. "\");";
		if ($conn->multi_query ( $query )) {
			echo "New record created successfully<br />";
			do {
				if ($result = $conn->store_result ()) {
					$result->free();
				}
			} while ( $conn->more_results () && $conn->next_result () );
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}
}
$conn->close ();
?>
<!DOCTYPE html>
<html>
<head>
<style type="text/css">
html, body, #map-canvas {
	height: 80%;
	margin: 0;
	padding: 0;
}
</style>
<script type="text/javascript"
	src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBGHfbO0-f12Zl2IhbFRp6NFEvtyY1zgGE">
</script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script type = "text/javascript">
	var map;
	var crimeType = 8;
	function calculate() {
		var data =  <?php echo json_encode($data); ?>;
		for (var z = 1; z < data.length; z++) {
			var loc = data[z][1];
			map.data.forEach(function (feature) {
				if (feature.getProperty('AREA_NAME').indexOf(loc) != -1) {
					var a = JSON.parse(JSON.stringify(feature.getGeometry().getArray()));
					if (a.length == 1)
						a = a[0].aa;
					var coords = [];
					for (var i = 0; i < a.length; i++) {
						coords.push(new google.maps.LatLng(a[i].k, a[i].B));
					}
					var polygon = new google.maps.Polygon({
							paths : coords,
						});
	
					var mapProp = {
						center : {
							lat : 43.70,
							lng : -79.388752
						},
						zoom : 10,
					};
					var tempmap = new google.maps.Map(document.getElementById("googleMap"), mapProp);
					polygon.setMap(tempmap)
	
					var bounds = new google.maps.LatLngBounds();
					for (var i = 0; i < polygon.getPath().getLength(); i++) {
						bounds.extend(polygon.getPath().getAt(i));
					}
					var sw = bounds.getSouthWest();
					var ne = bounds.getNorthEast();
					for (var j = 8; j < 11; j++) {
						var count = 0;
						var dat = data[0][j];
						var num = data[z][j];
						var sid = feature.getProperty('AREA_MUNI');
						var aid = feature.getProperty('AREA_S_CD');
						var lat = [];
						var lng = [];
						while (count < num) {
							var ptLat = Math.random() * (ne.lat() - sw.lat()) + sw.lat();
							var ptLng = Math.random() * (ne.lng() - sw.lng()) + sw.lng();
							var point = new google.maps.LatLng(ptLat, ptLng);
							if (google.maps.geometry.poly.containsLocation(point, polygon)) {
								lat[count] = ptLat;
								lng[count] = ptLng;
								count++;
								/*var circle = new google.maps.Circle({
								fillColor: 'red',
								fillOpacity: 1,
								strokeOpacity:0.2,
								radius: 30,
								center:point
								});
								circle.setMap(tempmap);
								//createMarker(tempmap, point,"marker "+i);*/
							}
						}
	
						$.ajax({
							url : "db.php",
							type : "POST",
							data : {
								'lat[]' : lat,
								'lng[]' : lng,
								'sid' : sid,
								'aid' : aid,
								'dat' : dat,
								'crimetype' : crimeType
							},
							success : function (response) {
								document.getElementById("SQLbox").innerHTML = response;
							},
							error : function () {
								document.getElementById("SQLbox").innerHTML = response;
							}
						});
					}
				}
			});
		}
	}
	function createMarker(map, point, content) {
		var marker = new google.maps.Marker({
				position : point,
				map : map
			});
		google.maps.event.addListener(marker, "click", function (evt) {
			infowindow.setContent(content + "<br>" + marker.getPosition().toUrlValue(6));
			infowindow.open(map, marker);
		});
		return marker;
	}
	function initialize() {
		var mapOptions = {
			center : {
				lat : 43.70,
				lng : -79.388752
			},
			zoom : 12,
			//mapTypeId: google.maps.MapTypeId.TERRAIN,
			mapTypeControl : false
		};
		map = new google.maps.Map(document.getElementById('map-canvas'),
				mapOptions);
		//map.data.loadGeoJson('data/police_division_wgs84.geojson');
		map.data.loadGeoJson('data/map.geojson');
		var featureStyle = {
			fillColor : 'green',
			strokeWeight : 0.5
		}
		map.data.setStyle(featureStyle);
		var infowindow = new google.maps.InfoWindow();
	
		map.data.addListener('click', function (e) {
			var info = e.feature.getProperty('AREA_NAME');
			infowindow.setContent("<div style='width:200px; text-align: center;'>"
				 + e.feature.getProperty('AREA_NAME') + "<br />" + e.feature.getProperty('AREA_MUNI') + "</div>");
			infowindow.setPosition(e.latLng);
			//infowindow.setOptions({pixelOffset: new google.maps.Size(0,-30)});
			infowindow.open(map);
			/*var bounds=e.feature.getProperty('bounds');
			if(bounds){
			alert('bounds:\n'+bounds.toString());
			}*/
	
		});
		/*
		google.maps.event.addListener(map.data,'addfeature',function(e){
		if(e.feature.getGeometry().getType()==='Polygon'){
		var bounds=new google.maps.LatLngBounds();
	
		e.feature.getGeometry().getArray().forEach(function(path){
	
		path.getArray().forEach(function(latLng){bounds.extend(latLng);})
	
		});
		e.feature.setProperty('bounds',bounds);
	
		//new google.maps.Rectangle({map:map,bounds:bounds,clickable:false,strokeWeight:0.0})
	
		}
		});*/
	
	}
	google.maps.event.addDomListener(window, 'load', function () {
		initialize();
		/*setTimeout(function () {
			calculate();
		}, 1000);*/
	});

</script>

</head>
<body>
	<div id="map-canvas"></div>
	<div id="info-box">hello</div>
	<div id="SQLbox"></div>
	<div id="googleMap" style="width: 900px; height: 600px;"></div>
</body>
</html>
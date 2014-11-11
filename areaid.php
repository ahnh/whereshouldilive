<?php
if (isset ( $_POST ['sid'] )) {
	$sid = $_POST ['sid'];
}
if (isset ( $_POST ['aid'] )) {
	$aid = $_POST ['aid'];
}
if (isset ( $_POST ['lat'] )) {
	$lat = $_POST ['lat'];
}
if (isset ( $_POST ['lng'] )) {
	$lng = $_POST ['lng'];
}
if (isset ( $_POST ['dat'] )) {
	$dat = $_POST ['dat'] . "-01-01";
	$dat = new DateTime ( $dat );
}
if (isset ( $_POST ['crimetype'] )) {
	$crimetype = $_POST ['crimetype'];
}
if ($dat && $sid && $aid && $lat && $lng && $crimetype) {
	$conn = mysqli_connect ( "localhost", "root", "", "db190263_should" );
	
	// Check connection
	if (mysqli_connect_errno ()) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error ();
	}
	for($x = 0; $x < count ( $lat ); $x ++) {
		$query = "INSERT INTO db190263_should.coordinates (latitude, longitude) VALUES(" . $lat [$x] . "," . $lng [$x] . ");";
		$query .= "INSERT INTO db190263_should.crime (crime_type, date, sector_id, coordinates, area_id)
		values (" . $crimetype . ", \"" . $dat->format ( 'Y-m-d' ) . "\", (select id from sector where name=\"" . $sid . "\"), LAST_INSERT_ID(), " . $aid . ");";
		
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
	$conn->close ();
}
?>
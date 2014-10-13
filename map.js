<script type="text/javascript">
	alert("hello");
	function initialize() {
		var mapOptions = {
			center: { lat: 43.654956, lng: -79.388752},
			zoom: 12
		};
		var map = new google.maps.Map(document.getElementById('map-canvas'),
		mapOptions);
	}
	google.maps.event.addDomListener(window, 'load', initialize);
</script>
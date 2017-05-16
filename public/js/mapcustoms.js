$(function(){

    if ( $( "#adoption_map" ).length ) {

        var tiles = L.tileLayer('https://c.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 20,
                attribution: '&copy; 2017 Street Sweeping Maps'
            }),
            latlng = new L.LatLng(38.2549359, -85.7614377);

        var map = new L.Map('adoption_map', {center: latlng, zoom: 13, layers: [tiles]});
	

        var markers = new L.MarkerClusterGroup();
        var markersList = [];

        function populate() {
            doAjaxCall('get', '/admin/getmapdata', 'json', {}, function (result) {
                $.each(result, function (i, val) {
                    var popuphtml = '<ul>' +
                        '<li>Address: ' + val.AlertAddress + '</li>' +
                        '<li>Alert Notification Value: <a href="mailto:' + val.NotificationValue + '">' + val.NotificationValue + '</a></li>' +
                        '<li>Area / Route: ' + val.PickupAreaID + '</li>' +
                        '<li>Date Added: ' + val.DateAdded + '</li>' +
                        '<li>Council District: ' + val.council_district + '</li>' +
                        '</ul>';
                    var m = new L.Marker([val.latitude, val.longitude]).bindPopup(popuphtml);
                    markersList.push(m);
                    markers.addLayer(m);
                });
            });
            return false;
        }

        populate();
        map.addLayer(markers);
    }
});

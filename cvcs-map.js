$(document).ready(function() {
  // Load the map
  // -------------------
  L.mapbox.accessToken = 'pk.eyJ1IjoicGhheWVzIiwiYSI6InNHMlkzQUkifQ.C9wmsbr-8tAtViMNb1wEcA';
  var map = L.mapbox.map('map', 'phayes.jjf0jnd4', {minZoom: 10});

  map.setMaxBounds(L.latLngBounds(L.latLng(49.2, -125.8), L.latLng(50, -124.4)));

  //var googleLayer = new L.Google('TERRAIN');
  //map.addLayer(googleLayer, true);

  var sei_layer   = L.mapbox.tileLayer('phayes.CVCS_SEI').addTo(map);
  var pro_layer   = L.mapbox.tileLayer('phayes.CVCS').addTo(map);

  var sei_grid    = L.mapbox.gridLayer('phayes.CVCS_SEI').addTo(map);
  var pro_grid    = L.mapbox.gridLayer('phayes.CVCS').addTo(map);

  var sei_control = L.mapbox.gridControl(sei_grid).addTo(map);
  var pro_control = L.mapbox.gridControl(pro_grid).addTo(map);

  // Forest Loss map
  var fl_layer = L.tileLayer('https://earthengine.google.org/static/hansen_2014/loss_tree_gain/{z}/{x}/{y}.png');

  // Scale bar
  L.control.scale().addTo(map);

  // Legend interactions
  // -------------------
  $('#legend').hover(function(event) {
    $('.map-tooltip').hide();
    event.stopPropagation();
  });

  var showFC = function() {
    $('#sei-legend').hide();
    $('#fc-legend').show();
    
    map.addLayer(fl_layer);
    map.removeLayer(sei_layer);
    map.removeLayer(sei_grid);
    map.removeLayer(pro_layer);
    map.removeLayer(pro_grid);

    $('.map-tooltip').hide();
    
    // Zoom out if we're too far zoomed in
    if (map.getZoom() > 12) {
      map.setZoom(12);
    }
    $('#fc-check').prop('checked', true);
  }

  var hideFC = function() {
    $('#sei-legend').show();
    $('#fc-legend').hide();

    map.addLayer(sei_layer);
    map.addLayer(pro_layer);
    map.addLayer(sei_grid);
    map.addLayer(pro_grid);
    map.removeLayer(fl_layer);
    $('#fc-check').prop('checked', false);
  }

  $('#fc-check').change(function() {
    history.pushState({showfc: $(this).is(':checked')}, null, window.location.pathname + '?showfc=' + $(this).is(':checked'));
    if ($(this).is(':checked')) {
      showFC();
    }
    else {
      hideFC();
    }
  });

  // Hanndle popstate for forward / back buttons
  window.onpopstate = function(event) {
    if (event.state && event.state.showfc) {
      showFC();
    }
    else {
      hideFC();
    }
  };

  // Tooltips for types
  var intactText = "<h5>Sensitive Ecosystem - Intact</h5> No human disturbance is observable at the assessment scale. The ecosystem is viable i.e. its native components are relatively undamaged and as such, it is expected to persist intact or follow natural successional processes over time.";
  var degradedText = "<h5>Sensitive Ecosystem - Degraded</h5> Human activities have caused disturbance but the ecosystem is still considered to be viable. ";
  var lostText = "<h5>Sensitive Ecosystem - Lost</h5> Human activities have caused disturbance to the extent that the ecosystem is no longer viable (the ecosystem is so small or has so much disturbance that it would not be expected to persist over time).";

  var $controlTip = $('.leaflet-control-grid.map-tooltip.leaflet-control').first();

  $('#intact-sei').hover(function() {
    $controlTip.html("<div class='legend-sei-desc'>" + intactText + "</div>").show();
  }, function() {
    $controlTip.hide();
  });

  $('#degraded-sei').hover(function() {
    $controlTip.html("<div class='legend-sei-desc'>" + degradedText + "</div>").show();
  }, function() {
    $controlTip.hide();
  });

  $('#lost-sei').hover(function() {
    $controlTip.html("<div class='legend-sei-desc'>" + lostText + "</div>").show();
  }, function() {
    $controlTip.hide();
  });




  // Sendmail interactions
  // ---------------------
  $('#sendmail-select').hover(
  function() {
    $(this).animate({
      height: "+=10", 
    });
  },
  function() {
    $(this).animate({
      height: "-=10", 
    });
  } 
  );
  $('#sendmail-close').click(function() {
    $('#sendmail').hide();
    $('#sendmail-select').show();
  });
  $('#sendmail-select').click(function() {
    $('#sendmail').show();
    $('#sendmail-select').hide();
  });
  $('#mapintro-close, #mapintro-done').click(function() {
    $('#mapintro').hide();
    $('#sendmail-select').show();
    $('#legend').show();
  });
  map.on('click', function() {
    $('#sendmail').hide();
    $('#mapintro').hide();
    $('#sendmail-select').show();
    $('#legend').show();
  });

});


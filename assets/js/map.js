/**
 * Skate Spots Map Functionality
 * This file provides additional map features and interactions
 */

(function($) {
    'use strict';
    
    // Custom marker icon (optional)
    var skateIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
    
    // Marker clustering (if needed for many spots)
    // This would require MarkerCluster plugin
    
    // Map utility functions
    window.SkateMapUtils = {
        
        // Get user location
        getUserLocation: function(callback) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        callback({
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        });
                    },
                    function(error) {
                        console.error('Geolocation error:', error);
                        callback(null);
                    }
                );
            } else {
                callback(null);
            }
        },
        
        // Calculate distance between two points (in km)
        calculateDistance: function(lat1, lon1, lat2, lon2) {
            var R = 6371; // Radius of the earth in km
            var dLat = this.deg2rad(lat2 - lat1);
            var dLon = this.deg2rad(lon2 - lon1);
            var a = 
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(this.deg2rad(lat1)) * Math.cos(this.deg2rad(lat2)) * 
                Math.sin(dLon/2) * Math.sin(dLon/2);
            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            var d = R * c; // Distance in km
            return d;
        },
        
        deg2rad: function(deg) {
            return deg * (Math.PI/180);
        },
        
        // Format distance for display
        formatDistance: function(km) {
            if (km < 1) {
                return Math.round(km * 1000) + ' m';
            } else {
                return km.toFixed(1) + ' km';
            }
        }
    };
    
})(jQuery);
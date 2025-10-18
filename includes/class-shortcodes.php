<?php
/**
 * Shortcodes handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class Skate_Spots_Shortcodes {
    
    public function __construct() {
        add_shortcode('skate_spots_map', array($this, 'spots_map'));
        add_shortcode('skate_movies_list', array($this, 'movies_list'));
        add_shortcode('skate_scenes_list', array($this, 'scenes_list'));
        add_shortcode('skate_skaters_list', array($this, 'skaters_list'));
    }
    
    /**
     * Map showing all approved skate spots
     */
    public function spots_map($atts) {
        $atts = shortcode_atts(array(
            'height' => '500px',
            'zoom' => 13
        ), $atts);
        
        // Get only approved spots (status = 1)
        $spots = Skate_Spots::get_approved(1000);
        
        // Filter out spots without coordinates
        $spots = array_filter($spots, function($spot) {
            return !empty($spot->latitude) && !empty($spot->longitude);
        });
        
        // Sort by created_at DESC to get newest first
        usort($spots, function($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });
        
        $spots_json = json_encode(array_values($spots));
        $default_zoom = intval($atts['zoom']);
        
        ob_start();
        ?>
        <div class="skate-spots-map-container">
            <div id="skate-spots-map" style="height: <?php echo esc_attr($atts['height']); ?>; width: 100%;"></div>
            <?php if (empty($spots)): ?>
                <p class="map-notice">No approved spots to display yet. Be the first to add one!</p>
            <?php else: ?>
                <p class="map-notice">Showing <?php echo count($spots); ?> approved spot<?php echo count($spots) != 1 ? 's' : ''; ?></p>
            <?php endif; ?>
        </div>
        <script>
        jQuery(document).ready(function($) {
            var spots = <?php echo $spots_json; ?>;
            var defaultZoom = <?php echo $default_zoom; ?>;
            
            if (spots.length === 0) {
                // No spots to display
                $('#skate-spots-map').html('<div style="display: flex; align-items: center; justify-content: center; height: 100%; background: #f5f5f5; color: #666;"><p>No spots available yet</p></div>');
                return;
            }
            
            // Get the most recent spot (first in array after sorting)
            var latestSpot = spots[0];
            var centerLat = parseFloat(latestSpot.latitude);
            var centerLng = parseFloat(latestSpot.longitude);
            
            // Initialize map centered on the latest spot
            var map = L.map('skate-spots-map').setView([centerLat, centerLng], defaultZoom);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19,
                minZoom: 2
            }).addTo(map);
            
            // Array to store all markers for bounds
            var markers = [];
            
            // Add markers for all spots
            spots.forEach(function(spot, index) {
                if (spot.latitude && spot.longitude) {
                    var lat = parseFloat(spot.latitude);
                    var lng = parseFloat(spot.longitude);
                    
                    // Create custom icon for the latest spot (larger, different color)
                    var markerIcon = L.icon({
                        iconUrl: index === 0 
                            ? 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png'
                            : 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                        iconSize: index === 0 ? [30, 49] : [25, 41],
                        iconAnchor: index === 0 ? [15, 49] : [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    });
                    
                    var marker = L.marker([lat, lng], {icon: markerIcon});
                    
                    var popupContent = '<div class="spot-popup">';
                    
                    // Add "NEW!" badge for the latest spot
                    if (index === 0) {
                        popupContent += '<span class="new-badge">🆕 NEWEST SPOT</span>';
                    }
                    
                    popupContent += '<h3>' + spot.title + '</h3>';
                    
                    if (spot.description) {
                        popupContent += '<p>' + spot.description + '</p>';
                    }
                    
                    if (spot.address) {
                        popupContent += '<p><strong>Address:</strong><br>' + spot.address;
                        if (spot.city || spot.state || spot.zip || spot.country) {
                            popupContent += '<br>';
                            if (spot.city) popupContent += spot.city;
                            if (spot.state) popupContent += ', ' + spot.state;
                            if (spot.zip) popupContent += ' ' + spot.zip;
                            if (spot.country) popupContent += '<br>' + spot.country;
                        }
                        popupContent += '</p>';
                    }
                    
                    if (spot.spot_type) {
                        popupContent += '<p><strong>Type:</strong> ' + spot.spot_type + '</p>';
                    }
                    
                    if (spot.image_url) {
                        popupContent += '<img src="' + spot.image_url + '" style="max-width: 200px; height: auto; border-radius: 4px; margin-top: 10px;" />';
                    }
                    
                    popupContent += '</div>';
                    
                    marker.bindPopup(popupContent);
                    marker.addTo(map);
                    markers.push(marker);
                    
                    // Auto-open popup for the latest spot
                    if (index === 0) {
                        marker.openPopup();
                    }
                }
            });
            
            // If there are multiple spots, add a button to fit all markers in view
            if (spots.length > 1) {
                var fitBoundsControl = L.control({position: 'topright'});
                
                fitBoundsControl.onAdd = function(map) {
                    var div = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
                    div.innerHTML = '<a href="#" title="Show all spots" style="background: white; padding: 5px 10px; text-decoration: none; display: block; color: #333; font-weight: bold;">Show All</a>';
                    
                    div.onclick = function(e) {
                        e.preventDefault();
                        var group = new L.featureGroup(markers);
                        map.fitBounds(group.getBounds().pad(0.1));
                        return false;
                    };
                    
                    return div;
                };
                
                fitBoundsControl.addTo(map);
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * List all approved movies
     */
    public function movies_list($atts) {
        $atts = shortcode_atts(array(
            'limit' => 100
        ), $atts);
        
        $movies = Skate_Movies::get_approved(intval($atts['limit']));
        
        ob_start();
        ?>
        <div class="skate-movies-list">
            <?php if (!empty($movies)): ?>
                <div class="movies-grid">
                    <?php foreach ($movies as $movie): ?>
                        <div class="movie-item">
                            <h3><?php echo esc_html($movie->title); ?></h3>
                            <?php if ($movie->release_date): ?>
                                <p class="release-date">
                                    <strong>Released:</strong> 
                                    <?php echo esc_html(date('F j, Y', strtotime($movie->release_date))); ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($movie->director): ?>
                                <p class="director">
                                    <strong>Director:</strong> <?php echo esc_html($movie->director); ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($movie->description): ?>
                                <p class="description"><?php echo wp_kses_post($movie->description); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No movies found.</p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * List all approved scenes
     */
    public function scenes_list($atts) {
        $atts = shortcode_atts(array(
            'limit' => 100
        ), $atts);
        
        $scenes = Skate_Scenes::get_all_with_details(1);
        
        ob_start();
        ?>
        <div class="skate-scenes-list">
            <?php if (!empty($scenes)): ?>
                <div class="scenes-grid">
                    <?php foreach ($scenes as $scene): ?>
                        <div class="scene-item">
                            <?php if ($scene->scene_title): ?>
                                <h3><?php echo esc_html($scene->scene_title); ?></h3>
                            <?php endif; ?>
                            
                            <div class="scene-meta">
                                <?php if ($scene->movie_title): ?>
                                    <p><strong>Movie:</strong> <?php echo esc_html($scene->movie_title); ?></p>
                                <?php endif; ?>
                                
                                <?php if ($scene->spot_title): ?>
                                    <p><strong>Spot:</strong> <?php echo esc_html($scene->spot_title); ?></p>
                                <?php endif; ?>
                                
                                <?php if ($scene->timestamp): ?>
                                    <p><strong>Timestamp:</strong> <?php echo esc_html($scene->timestamp); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($scene->scene_description): ?>
                                <p class="description"><?php echo wp_kses_post($scene->scene_description); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($scene->video_url): ?>
                                <p><a href="<?php echo esc_url($scene->video_url); ?>" target="_blank" class="video-link">Watch Video</a></p>
                            <?php endif; ?>
                            
                            <?php
                            $skaters = Skate_Scenes::get_skaters($scene->id);
                            if (!empty($skaters)):
                            ?>
                                <div class="scene-skaters">
                                    <strong>Skaters:</strong>
                                    <?php 
                                    $names = array_map(function($s) { return esc_html($s->name); }, $skaters);
                                    echo implode(', ', $names);
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No scenes found.</p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * List all approved skaters
     */
    public function skaters_list($atts) {
        $atts = shortcode_atts(array(
            'limit' => 100
        ), $atts);
        
        $skaters = Skate_Skaters::get_approved(intval($atts['limit']));
        
        ob_start();
        ?>
        <div class="skate-skaters-list">
            <?php if (!empty($skaters)): ?>
                <div class="skaters-grid">
                    <?php foreach ($skaters as $skater): ?>
                        <div class="skater-item">
                            <h3><?php echo esc_html($skater->name); ?></h3>
                            
                            <?php if ($skater->bio): ?>
                                <p class="bio"><?php echo wp_kses_post($skater->bio); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($skater->social_url): ?>
                                <p>
                                    <a href="<?php echo esc_url($skater->social_url); ?>" target="_blank" class="social-link">
                                        Follow on Social Media
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No skaters found.</p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
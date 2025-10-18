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
            'height' => '500px'
        ), $atts);
        
        $spots = Skate_Spots::get_approved(1000);
        $spots_json = json_encode($spots);
        
        ob_start();
        ?>
        <div class="skate-spots-map-container">
            <div id="skate-spots-map" style="height: <?php echo esc_attr($atts['height']); ?>; width: 100%;"></div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            var spots = <?php echo $spots_json; ?>;
            
            // Initialize map
            var map = L.map('skate-spots-map').setView([37.7749, -122.4194], 4);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
            
            // Add markers
            spots.forEach(function(spot) {
                if (spot.latitude && spot.longitude) {
                    var marker = L.marker([parseFloat(spot.latitude), parseFloat(spot.longitude)]);
                    
                    var popupContent = '<div class="spot-popup">' +
                        '<h3>' + spot.title + '</h3>';
                    
                    if (spot.description) {
                        popupContent += '<p>' + spot.description + '</p>';
                    }
                    
                    if (spot.address) {
                        popupContent += '<p><strong>Address:</strong> ' + spot.address;
                        if (spot.city) popupContent += ', ' + spot.city;
                        if (spot.state) popupContent += ', ' + spot.state;
                        if (spot.zip) popupContent += ' ' + spot.zip;
                        if (spot.country) popupContent += ', ' + spot.country;
                        popupContent += '</p>';
                    }
                    
                    if (spot.spot_type) {
                        popupContent += '<p><strong>Type:</strong> ' + spot.spot_type + '</p>';
                    }
                    
                    if (spot.image_url) {
                        popupContent += '<img src="' + spot.image_url + '" style="max-width: 200px; height: auto;" />';
                    }
                    
                    popupContent += '</div>';
                    
                    marker.bindPopup(popupContent);
                    marker.addTo(map);
                }
            });
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
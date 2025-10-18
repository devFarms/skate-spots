<?php
/**
 * Frontend Forms Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class Skate_Spots_Frontend_Forms {
    
    public function __construct() {
        // Form shortcodes
        add_shortcode('skate_spot_form', array($this, 'spot_form'));
        add_shortcode('skate_movie_form', array($this, 'movie_form'));
        add_shortcode('skate_scene_form', array($this, 'scene_form'));
        add_shortcode('skate_skater_form', array($this, 'skater_form'));
        
        // AJAX handlers
        add_action('wp_ajax_submit_skate_spot', array($this, 'handle_spot_submission'));
        add_action('wp_ajax_nopriv_submit_skate_spot', array($this, 'handle_spot_submission'));
        
        add_action('wp_ajax_submit_skate_movie', array($this, 'handle_movie_submission'));
        add_action('wp_ajax_nopriv_submit_skate_movie', array($this, 'handle_movie_submission'));
        
        add_action('wp_ajax_submit_skate_scene', array($this, 'handle_scene_submission'));
        add_action('wp_ajax_nopriv_submit_skate_scene', array($this, 'handle_scene_submission'));
        
        add_action('wp_ajax_submit_skate_skater', array($this, 'handle_skater_submission'));
        add_action('wp_ajax_nopriv_submit_skate_skater', array($this, 'handle_skater_submission'));
    }
    
    /**
     * Check if user can bypass approval
     */
    private function can_bypass_approval() {
        if (!is_user_logged_in()) {
            return false;
        }
        
        $user = wp_get_current_user();
        $allowed_roles = array('editor', 'administrator');
        
        return !empty(array_intersect($allowed_roles, $user->roles));
    }
    
    /**
     * Spot submission form
     */
    public function spot_form($atts) {
        ob_start();
        ?>
        <div class="skate-form-container">
            <form id="skate-spot-form" class="skate-form" enctype="multipart/form-data">
                <?php wp_nonce_field('skate_spot_form', 'skate_spot_nonce'); ?>
                
                <div id="form-messages"></div>
                
                <div class="form-info">
                    <strong>ℹ️ Location Info:</strong> Enter the complete address details. We'll automatically find the GPS coordinates to place your spot on the map. The more details you provide (state and zip code), the more accurate the location will be.
                </div>
                
                <div class="form-group">
                    <label for="spot_title">Title *</label>
                    <input type="text" id="spot_title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="spot_description">Description</label>
                    <textarea id="spot_description" name="description" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="spot_address">Street Address *</label>
                    <input type="text" id="spot_address" name="address" required>
                    <small>e.g., 123 Main Street</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="spot_city">City *</label>
                        <input type="text" id="spot_city" name="city" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="spot_state">State/Province</label>
                        <input type="text" id="spot_state" name="state">
                        <small>e.g., California, Ontario</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="spot_zip">Zip/Postal Code</label>
                        <input type="text" id="spot_zip" name="zip">
                    </div>
                    
                    <div class="form-group">
                        <label for="spot_country">Country *</label>
                        <input type="text" id="spot_country" name="country" required placeholder="U.S.A" value="U.S.A">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="spot_type">Spot Type</label>
                    <select id="spot_type" name="spot_type">
                        <option value="">Select Type</option>
                        <option value="street">Street</option>
                        <option value="park">Park</option>
                        <option value="bowl">Bowl</option>
                        <option value="vert">Vert</option>
                        <option value="diy">DIY</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="spot_image">Image URL</label>
                    <input type="url" id="spot_image" name="image_url">
                </div>
                
                <button type="submit" class="submit-btn">Submit Spot</button>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#skate-spot-form').on('submit', function(e) {
                e.preventDefault();
                
                var $submitBtn = $(this).find('.submit-btn');
                var originalText = $submitBtn.text();
                
                // Disable button and show loading state
                $submitBtn.prop('disabled', true).text('Geocoding address...');
                
                var formData = new FormData(this);
                formData.append('action', 'submit_skate_spot');
                
                $.ajax({
                    url: skateSpots.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#form-messages').html('<div class="success-message">' + response.data.message + '</div>');
                            $('#skate-spot-form')[0].reset();
                        } else {
                            $('#form-messages').html('<div class="error-message">' + response.data.message + '</div>');
                        }
                        // Re-enable button
                        $submitBtn.prop('disabled', false).text(originalText);
                    },
                    error: function() {
                        $('#form-messages').html('<div class="error-message">An error occurred. Please try again.</div>');
                        $submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Handle spot submission
     */
    public function handle_spot_submission() {
        check_ajax_referer('skate_spot_form', 'skate_spot_nonce');
        
        $status = $this->can_bypass_approval() ? 1 : 0;
        
        // Geocode the address to get coordinates
        $address = sanitize_text_field($_POST['address']);
        $city = sanitize_text_field($_POST['city']);
        $state = sanitize_text_field($_POST['state']);
        $zip = sanitize_text_field($_POST['zip']);
        $country = sanitize_text_field($_POST['country']);
        
        // Build full address for geocoding
        $full_address = $address;
        if (!empty($city)) {
            $full_address .= ', ' . $city;
        }
        if (!empty($state)) {
            $full_address .= ', ' . $state;
        }
        if (!empty($zip)) {
            $full_address .= ' ' . $zip;
        }
        if (!empty($country)) {
            $full_address .= ', ' . $country;
        }
        
        // Geocode the address
        $coordinates = $this->geocode_address($full_address);
        
        if (!$coordinates) {
            wp_send_json_error(array('message' => 'Unable to geocode the address. Please check the address and try again.'));
        }
        
        $data = array(
            'title' => sanitize_text_field($_POST['title']),
            'description' => sanitize_textarea_field($_POST['description']),
            'latitude' => $coordinates['lat'],
            'longitude' => $coordinates['lon'],
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'zip' => $zip,
            'country' => $country,
            'spot_type' => sanitize_text_field($_POST['spot_type']),
            'image_url' => esc_url_raw($_POST['image_url']),
            'status' => $status
        );
        
        $result = Skate_Spots::create($data);
        
        if ($result) {
            $message = $status == 1 
                ? 'Spot successfully added!' 
                : 'Spot submitted successfully! It will be reviewed by an administrator.';
            
            wp_send_json_success(array('message' => $message));
        } else {
            wp_send_json_error(array('message' => 'Failed to submit spot. Please try again.'));
        }
    }
    
    /**
     * Geocode an address to lat/lon coordinates
     * Using Nominatim (OpenStreetMap) - free, no API key required
     */
    private function geocode_address($address) {
        // Use WordPress HTTP API
        $url = 'https://nominatim.openstreetmap.org/search';
        
        $args = array(
            'q' => $address,
            'format' => 'json',
            'limit' => 1
        );
        
        $url = add_query_arg($args, $url);
        
        // Set user agent (required by Nominatim)
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'WordPress Skate Spots Plugin'
            )
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);
        
        if (empty($data) || !isset($data[0]->lat) || !isset($data[0]->lon)) {
            return false;
        }
        
        return array(
            'lat' => floatval($data[0]->lat),
            'lon' => floatval($data[0]->lon)
        );
    }
    
    /**
     * Movie submission form
     */
    public function movie_form($atts) {
        ob_start();
        ?>
        <div class="skate-form-container">
            <form id="skate-movie-form" class="skate-form">
                <?php wp_nonce_field('skate_movie_form', 'skate_movie_nonce'); ?>
                
                <div id="form-messages"></div>
                
                <div class="form-group">
                    <label for="movie_title">Title *</label>
                    <input type="text" id="movie_title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="movie_release_date">Release Date</label>
                    <input type="date" id="movie_release_date" name="release_date">
                </div>
                
                <div class="form-group">
                    <label for="movie_director">Director</label>
                    <input type="text" id="movie_director" name="director">
                </div>
                
                <div class="form-group">
                    <label for="movie_description">Description</label>
                    <textarea id="movie_description" name="description" rows="4"></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Submit Movie</button>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#skate-movie-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                formData += '&action=submit_skate_movie';
                
                $.ajax({
                    url: skateSpots.ajax_url,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#form-messages').html('<div class="success-message">' + response.data.message + '</div>');
                            $('#skate-movie-form')[0].reset();
                        } else {
                            $('#form-messages').html('<div class="error-message">' + response.data.message + '</div>');
                        }
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Handle movie submission
     */
    public function handle_movie_submission() {
        check_ajax_referer('skate_movie_form', 'skate_movie_nonce');
        
        $status = $this->can_bypass_approval() ? 1 : 0;
        
        $data = array(
            'title' => sanitize_text_field($_POST['title']),
            'release_date' => !empty($_POST['release_date']) ? sanitize_text_field($_POST['release_date']) : null,
            'director' => sanitize_text_field($_POST['director']),
            'description' => sanitize_textarea_field($_POST['description']),
            'status' => $status
        );
        
        $result = Skate_Movies::create($data);
        
        if ($result) {
            $message = $status == 1 
                ? 'Movie successfully added!' 
                : 'Movie submitted successfully! It will be reviewed by an administrator.';
            
            wp_send_json_success(array('message' => $message));
        } else {
            wp_send_json_error(array('message' => 'Failed to submit movie. Please try again.'));
        }
    }
    
    /**
     * Scene submission form
     */
    public function scene_form($atts) {
        $movies = Skate_Movies::get_approved();
        $spots = Skate_Spots::get_approved();
        
        ob_start();
        ?>
        <div class="skate-form-container">
            <form id="skate-scene-form" class="skate-form">
                <?php wp_nonce_field('skate_scene_form', 'skate_scene_nonce'); ?>
                
                <div id="form-messages"></div>
                
                <div class="form-group">
                    <label for="scene_movie">Movie *</label>
                    <select id="scene_movie" name="movie_id" required>
                        <option value="">Select Movie</option>
                        <?php foreach ($movies as $movie): ?>
                            <option value="<?php echo esc_attr($movie->id); ?>">
                                <?php echo esc_html($movie->title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="scene_spot">Spot *</label>
                    <select id="scene_spot" name="spot_id" required>
                        <option value="">Select Spot</option>
                        <?php foreach ($spots as $spot): ?>
                            <option value="<?php echo esc_attr($spot->id); ?>">
                                <?php echo esc_html($spot->title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="scene_title">Scene Title</label>
                    <input type="text" id="scene_title" name="scene_title">
                </div>
                
                <div class="form-group">
                    <label for="scene_description">Description</label>
                    <textarea id="scene_description" name="scene_description" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="scene_video">Video URL</label>
                    <input type="url" id="scene_video" name="video_url">
                </div>
                
                <div class="form-group">
                    <label for="scene_timestamp">Timestamp (e.g., 1:23:45)</label>
                    <input type="text" id="scene_timestamp" name="timestamp">
                </div>
                
                <button type="submit" class="submit-btn">Submit Scene</button>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#skate-scene-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                formData += '&action=submit_skate_scene';
                
                $.ajax({
                    url: skateSpots.ajax_url,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#form-messages').html('<div class="success-message">' + response.data.message + '</div>');
                            $('#skate-scene-form')[0].reset();
                        } else {
                            $('#form-messages').html('<div class="error-message">' + response.data.message + '</div>');
                        }
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Handle scene submission
     */
    public function handle_scene_submission() {
        check_ajax_referer('skate_scene_form', 'skate_scene_nonce');
        
        $status = $this->can_bypass_approval() ? 1 : 0;
        
        $data = array(
            'movie_id' => intval($_POST['movie_id']),
            'spot_id' => intval($_POST['spot_id']),
            'scene_title' => sanitize_text_field($_POST['scene_title']),
            'scene_description' => sanitize_textarea_field($_POST['scene_description']),
            'video_url' => esc_url_raw($_POST['video_url']),
            'timestamp' => sanitize_text_field($_POST['timestamp']),
            'status' => $status
        );
        
        $result = Skate_Scenes::create($data);
        
        if ($result) {
            $message = $status == 1 
                ? 'Scene successfully added!' 
                : 'Scene submitted successfully! It will be reviewed by an administrator.';
            
            wp_send_json_success(array('message' => $message));
        } else {
            wp_send_json_error(array('message' => 'Failed to submit scene. Please try again.'));
        }
    }
    
    /**
     * Skater submission form
     */
    public function skater_form($atts) {
        ob_start();
        ?>
        <div class="skate-form-container">
            <form id="skate-skater-form" class="skate-form">
                <?php wp_nonce_field('skate_skater_form', 'skate_skater_nonce'); ?>
                
                <div id="form-messages"></div>
                
                <div class="form-group">
                    <label for="skater_name">Name *</label>
                    <input type="text" id="skater_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="skater_bio">Bio</label>
                    <textarea id="skater_bio" name="bio" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="skater_social">Social Media URL</label>
                    <input type="url" id="skater_social" name="social_url">
                </div>
                
                <button type="submit" class="submit-btn">Submit Skater</button>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#skate-skater-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                formData += '&action=submit_skate_skater';
                
                $.ajax({
                    url: skateSpots.ajax_url,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#form-messages').html('<div class="success-message">' + response.data.message + '</div>');
                            $('#skate-skater-form')[0].reset();
                        } else {
                            $('#form-messages').html('<div class="error-message">' + response.data.message + '</div>');
                        }
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Handle skater submission
     */
    public function handle_skater_submission() {
        check_ajax_referer('skate_skater_form', 'skate_skater_nonce');
        
        $status = $this->can_bypass_approval() ? 1 : 0;
        
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'bio' => sanitize_textarea_field($_POST['bio']),
            'social_url' => esc_url_raw($_POST['social_url']),
            'status' => $status
        );
        
        $result = Skate_Skaters::create($data);
        
        if ($result) {
            $message = $status == 1 
                ? 'Skater successfully added!' 
                : 'Skater submitted successfully! It will be reviewed by an administrator.';
            
            wp_send_json_success(array('message' => $message));
        } else {
            wp_send_json_error(array('message' => 'Failed to submit skater. Please try again.'));
        }
    }
}
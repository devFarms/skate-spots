<?php
/**
 * User Authentication Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class Skate_Spots_User_Auth {
    
    public function __construct() {
        // Registration and login shortcodes
        add_shortcode('skate_register_form', array($this, 'register_form'));
        add_shortcode('skate_login_form', array($this, 'login_form'));
        add_shortcode('skate_logout_link', array($this, 'logout_link'));
        
        // AJAX handlers
        add_action('wp_ajax_nopriv_skate_register', array($this, 'handle_registration'));
        add_action('wp_ajax_nopriv_skate_login', array($this, 'handle_login'));
        add_action('wp_ajax_skate_logout', array($this, 'handle_logout'));
        
        // Prevent non-admins from accessing wp-admin
        add_action('admin_init', array($this, 'restrict_admin_access'));
    }
    
    /**
     * Registration form
     */
    public function register_form($atts) {
        if (is_user_logged_in()) {
            return '<p>You are already logged in.</p>';
        }
        
        ob_start();
        ?>
        <div class="skate-auth-container">
            <form id="skate-register-form" class="skate-form">
                <?php wp_nonce_field('skate_register', 'skate_register_nonce'); ?>
                
                <h2>Register</h2>
                
                <div id="register-messages"></div>
                
                <div class="form-group">
                    <label for="register_username">Username *</label>
                    <input type="text" id="register_username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="register_email">Email *</label>
                    <input type="email" id="register_email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="register_password">Password *</label>
                    <input type="password" id="register_password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="register_password_confirm">Confirm Password *</label>
                    <input type="password" id="register_password_confirm" name="password_confirm" required>
                </div>
                
                <button type="submit" class="submit-btn">Register</button>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#skate-register-form').on('submit', function(e) {
                e.preventDefault();
                
                var password = $('#register_password').val();
                var passwordConfirm = $('#register_password_confirm').val();
                
                if (password !== passwordConfirm) {
                    $('#register-messages').html('<div class="error-message">Passwords do not match.</div>');
                    return;
                }
                
                var formData = $(this).serialize();
                formData += '&action=skate_register';
                
                $.ajax({
                    url: skateSpots.ajax_url,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#register-messages').html('<div class="success-message">' + response.data.message + '</div>');
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            $('#register-messages').html('<div class="error-message">' + response.data.message + '</div>');
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
     * Handle registration
     */
    public function handle_registration() {
        check_ajax_referer('skate_register', 'skate_register_nonce');
        
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        
        // Validate
        if (empty($username) || empty($email) || empty($password)) {
            wp_send_json_error(array('message' => 'All fields are required.'));
        }
        
        if (!is_email($email)) {
            wp_send_json_error(array('message' => 'Invalid email address.'));
        }
        
        if (username_exists($username)) {
            wp_send_json_error(array('message' => 'Username already exists.'));
        }
        
        if (email_exists($email)) {
            wp_send_json_error(array('message' => 'Email already registered.'));
        }
        
        // Create user
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error(array('message' => $user_id->get_error_message()));
        }
        
        // Set role to subscriber by default
        $user = new WP_User($user_id);
        $user->set_role('subscriber');
        
        // Log the user in
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        
        wp_send_json_success(array('message' => 'Registration successful! Redirecting...'));
    }
    
    /**
     * Login form
     */
    public function login_form($atts) {
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            return '<p>Welcome, ' . esc_html($user->display_name) . '! You are already logged in.</p>';
        }
        
        ob_start();
        ?>
        <div class="skate-auth-container">
            <form id="skate-login-form" class="skate-form">
                <?php wp_nonce_field('skate_login', 'skate_login_nonce'); ?>
                
                <h2>Login</h2>
                
                <div id="login-messages"></div>
                
                <div class="form-group">
                    <label for="login_username">Username or Email *</label>
                    <input type="text" id="login_username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="login_password">Password *</label>
                    <input type="password" id="login_password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="remember" value="1"> Remember Me
                    </label>
                </div>
                
                <button type="submit" class="submit-btn">Login</button>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#skate-login-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                formData += '&action=skate_login';
                
                $.ajax({
                    url: skateSpots.ajax_url,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#login-messages').html('<div class="success-message">' + response.data.message + '</div>');
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            $('#login-messages').html('<div class="error-message">' + response.data.message + '</div>');
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
     * Handle login
     */
    public function handle_login() {
        check_ajax_referer('skate_login', 'skate_login_nonce');
        
        $username = sanitize_text_field($_POST['username']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) ? true : false;
        
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember
        );
        
        $user = wp_signon($creds, false);
        
        if (is_wp_error($user)) {
            wp_send_json_error(array('message' => 'Invalid username or password.'));
        }
        
        wp_send_json_success(array('message' => 'Login successful! Redirecting...'));
    }
    
    /**
     * Logout link
     */
    public function logout_link($atts) {
        if (!is_user_logged_in()) {
            return '';
        }
        
        $atts = shortcode_atts(array(
            'text' => 'Logout',
            'redirect' => home_url()
        ), $atts);
        
        $logout_url = wp_logout_url($atts['redirect']);
        
        return '<a href="' . esc_url($logout_url) . '" class="skate-logout-link">' . esc_html($atts['text']) . '</a>';
    }
    
    /**
     * Restrict admin access to non-admins/editors
     */
    public function restrict_admin_access() {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }
        
        if (is_admin() && !current_user_can('edit_posts')) {
            wp_redirect(home_url());
            exit;
        }
    }
}
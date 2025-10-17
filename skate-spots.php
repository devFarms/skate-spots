<?php
/**
 * Plugin Name: Skate Spots
 * Plugin URI: https://example.com/skate-spots
 * Description: A comprehensive plugin for managing skate spots, movies, scenes, and skaters with custom database tables and front-end submission forms.
 * Version: 0.2
 * Author: Alex Green
 * Author URI: https://example.com
 * License: GPL v2 or later
 * Text Domain: skate-spots
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SKATE_SPOTS_VERSION', '1.0.0');
define('SKATE_SPOTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SKATE_SPOTS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SKATE_SPOTS_PLUGIN_FILE', __FILE__);

// Include required files
require_once SKATE_SPOTS_PLUGIN_DIR . 'includes/class-database.php';
require_once SKATE_SPOTS_PLUGIN_DIR . 'includes/class-spots.php';
require_once SKATE_SPOTS_PLUGIN_DIR . 'includes/class-movies.php';
require_once SKATE_SPOTS_PLUGIN_DIR . 'includes/class-scenes.php';
require_once SKATE_SPOTS_PLUGIN_DIR . 'includes/class-skaters.php';
require_once SKATE_SPOTS_PLUGIN_DIR . 'includes/class-shortcodes.php';
require_once SKATE_SPOTS_PLUGIN_DIR . 'includes/class-frontend-forms.php';
require_once SKATE_SPOTS_PLUGIN_DIR . 'includes/class-user-auth.php';
require_once SKATE_SPOTS_PLUGIN_DIR . 'includes/class-admin.php';

/**
 * Main plugin class
 */
class Skate_Spots_Plugin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize plugin
        add_action('plugins_loaded', array($this, 'init'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    public function activate() {
        Skate_Spots_Database::create_tables();
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    public function init() {
        // Initialize all components
        new Skate_Spots_Shortcodes();
        new Skate_Spots_Frontend_Forms();
        new Skate_Spots_User_Auth();
        new Skate_Spots_Admin();
    }
    
    public function enqueue_frontend_assets() {
        // CSS
        wp_enqueue_style(
            'skate-spots-frontend',
            SKATE_SPOTS_PLUGIN_URL . 'public/css/frontend-styles.css',
            array(),
            SKATE_SPOTS_VERSION
        );
        
        // JS
        wp_enqueue_script(
            'skate-spots-frontend',
            SKATE_SPOTS_PLUGIN_URL . 'public/js/frontend-scripts.js',
            array('jquery'),
            SKATE_SPOTS_VERSION,
            true
        );
        
        // Leaflet for maps
        wp_enqueue_style(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            array(),
            '1.9.4'
        );
        
        wp_enqueue_script(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            array(),
            '1.9.4',
            true
        );
        
        wp_enqueue_script(
            'skate-spots-map',
            SKATE_SPOTS_PLUGIN_URL . 'assets/js/map.js',
            array('jquery', 'leaflet'),
            SKATE_SPOTS_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('skate-spots-frontend', 'skateSpots', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('skate_spots_nonce')
        ));
    }
    
    public function enqueue_admin_assets($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'skate-spots') === false) {
            return;
        }
        
        wp_enqueue_style(
            'skate-spots-admin',
            SKATE_SPOTS_PLUGIN_URL . 'admin/css/admin-styles.css',
            array(),
            SKATE_SPOTS_VERSION
        );
    }
}

// Initialize the plugin
Skate_Spots_Plugin::get_instance();
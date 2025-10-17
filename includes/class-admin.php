<?php
/**
 * Admin Interface Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class Skate_Spots_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_post_skate_update_status', array($this, 'handle_status_update'));
        add_action('admin_post_skate_delete_entry', array($this, 'handle_delete'));
    }
    
    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            'Skate Spots',
            'Skate Spots',
            'edit_posts',
            'skate-spots',
            array($this, 'spots_page'),
            'dashicons-location',
            30
        );
        
        // Subpages
        add_submenu_page(
            'skate-spots',
            'Spots',
            'Spots',
            'edit_posts',
            'skate-spots',
            array($this, 'spots_page')
        );
        
        add_submenu_page(
            'skate-spots',
            'Movies',
            'Movies',
            'edit_posts',
            'skate-spots-movies',
            array($this, 'movies_page')
        );
        
        add_submenu_page(
            'skate-spots',
            'Scenes',
            'Scenes',
            'edit_posts',
            'skate-spots-scenes',
            array($this, 'scenes_page')
        );
        
        add_submenu_page(
            'skate-spots',
            'Skaters',
            'Skaters',
            'edit_posts',
            'skate-spots-skaters',
            array($this, 'skaters_page')
        );
    }
    
    /**
     * Spots admin page
     */
    public function spots_page() {
        $status_filter = isset($_GET['status']) ? intval($_GET['status']) : null;
        $spots = Skate_Spots::get_all($status_filter, 1000);
        
        include SKATE_SPOTS_PLUGIN_DIR . 'admin/views/spots-list.php';
    }
    
    /**
     * Movies admin page
     */
    public function movies_page() {
        $status_filter = isset($_GET['status']) ? intval($_GET['status']) : null;
        $movies = Skate_Movies::get_all($status_filter, 1000);
        
        include SKATE_SPOTS_PLUGIN_DIR . 'admin/views/movies-list.php';
    }
    
    /**
     * Scenes admin page
     */
    public function scenes_page() {
        $status_filter = isset($_GET['status']) ? intval($_GET['status']) : null;
        $scenes = Skate_Scenes::get_all_with_details($status_filter);
        
        include SKATE_SPOTS_PLUGIN_DIR . 'admin/views/scenes-list.php';
    }
    
    /**
     * Skaters admin page
     */
    public function skaters_page() {
        $status_filter = isset($_GET['status']) ? intval($_GET['status']) : null;
        $skaters = Skate_Skaters::get_all($status_filter, 1000);
        
        include SKATE_SPOTS_PLUGIN_DIR . 'admin/views/skaters-list.php';
    }
    
    /**
     * Handle status updates
     */
    public function handle_status_update() {
        if (!current_user_can('edit_posts')) {
            wp_die('Unauthorized');
        }
        
        check_admin_referer('skate_update_status');
        
        $type = sanitize_text_field($_POST['type']);
        $id = intval($_POST['id']);
        $status = intval($_POST['status']);
        $redirect = sanitize_text_field($_POST['redirect']);
        
        switch ($type) {
            case 'spot':
                Skate_Spots::update_status($id, $status);
                break;
            case 'movie':
                Skate_Movies::update_status($id, $status);
                break;
            case 'scene':
                Skate_Scenes::update_status($id, $status);
                break;
            case 'skater':
                Skate_Skaters::update_status($id, $status);
                break;
        }
        
        wp_redirect(admin_url($redirect . '&updated=1'));
        exit;
    }
    
    /**
     * Handle deletions
     */
    public function handle_delete() {
        if (!current_user_can('edit_posts')) {
            wp_die('Unauthorized');
        }
        
        check_admin_referer('skate_delete_entry');
        
        $type = sanitize_text_field($_POST['type']);
        $id = intval($_POST['id']);
        $redirect = sanitize_text_field($_POST['redirect']);
        
        switch ($type) {
            case 'spot':
                Skate_Spots::delete($id);
                break;
            case 'movie':
                Skate_Movies::delete($id);
                break;
            case 'scene':
                Skate_Scenes::delete($id);
                break;
            case 'skater':
                Skate_Skaters::delete($id);
                break;
        }
        
        wp_redirect(admin_url($redirect . '&deleted=1'));
        exit;
    }
}
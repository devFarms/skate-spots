<?php
/**
 * Database management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Skate_Spots_Database {
    
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Skate Spots Table
        $table_spots = $wpdb->prefix . 'skate_spots';
        $sql_spots = "CREATE TABLE $table_spots (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text,
            latitude decimal(10, 8),
            longitude decimal(11, 8),
            address varchar(255),
            city varchar(100),
            state varchar(100),
            zip varchar(20),
            country varchar(100),
            spot_type varchar(100),
            image_url varchar(500),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status tinyint(1) DEFAULT 0 COMMENT '0=pending, 1=approved, 2=rejected',
            PRIMARY KEY  (id),
            KEY status (status),
            KEY city (city),
            KEY state (state)
        ) $charset_collate;";
        
        // Movies Table
        $table_movies = $wpdb->prefix . 'skate_movies';
        $sql_movies = "CREATE TABLE $table_movies (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            release_date date,
            description text,
            director varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status tinyint(1) DEFAULT 0 COMMENT '0=pending, 1=approved, 2=rejected',
            PRIMARY KEY  (id),
            KEY status (status)
        ) $charset_collate;";
        
        // Scenes Table
        $table_scenes = $wpdb->prefix . 'skate_scenes';
        $sql_scenes = "CREATE TABLE $table_scenes (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            movie_id bigint(20) UNSIGNED NOT NULL,
            spot_id bigint(20) UNSIGNED NOT NULL,
            scene_title varchar(255),
            scene_description text,
            video_url varchar(500),
            timestamp varchar(50),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status tinyint(1) DEFAULT 0 COMMENT '0=pending, 1=approved, 2=rejected',
            PRIMARY KEY  (id),
            KEY movie_id (movie_id),
            KEY spot_id (spot_id),
            KEY status (status)
        ) $charset_collate;";
        
        // Skaters Table
        $table_skaters = $wpdb->prefix . 'skate_skaters';
        $sql_skaters = "CREATE TABLE $table_skaters (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            bio text,
            social_url varchar(500),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status tinyint(1) DEFAULT 0 COMMENT '0=pending, 1=approved, 2=rejected',
            PRIMARY KEY  (id),
            KEY status (status)
        ) $charset_collate;";
        
        // Skater-Scene Linking Table
        $table_skater_scenes = $wpdb->prefix . 'skate_skater_scenes';
        $sql_skater_scenes = "CREATE TABLE $table_skater_scenes (
            skater_id bigint(20) UNSIGNED NOT NULL,
            scene_id bigint(20) UNSIGNED NOT NULL,
            PRIMARY KEY  (skater_id, scene_id),
            KEY skater_id (skater_id),
            KEY scene_id (scene_id)
        ) $charset_collate;";
        
        // Execute table creation
        dbDelta($sql_spots);
        dbDelta($sql_movies);
        dbDelta($sql_scenes);
        dbDelta($sql_skaters);
        dbDelta($sql_skater_scenes);
        
        // Save database version
        update_option('skate_spots_db_version', SKATE_SPOTS_VERSION);
    }
    
    public static function get_status_label($status) {
        $labels = array(
            0 => 'Pending',
            1 => 'Approved',
            2 => 'Rejected'
        );
        return isset($labels[$status]) ? $labels[$status] : 'Unknown';
    }
}
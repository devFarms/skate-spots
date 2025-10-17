<?php
/**
 * Scenes CRUD operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Skate_Scenes {
    
    private static $table_name = 'skate_scenes';
    
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::$table_name;
    }
    
    public static function create($data) {
        global $wpdb;
        
        $table = self::get_table_name();
        
        $defaults = array(
            'movie_id' => 0,
            'spot_id' => 0,
            'scene_title' => '',
            'scene_description' => '',
            'video_url' => '',
            'timestamp' => '',
            'status' => 0
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $wpdb->insert(
            $table,
            $data,
            array('%d', '%d', '%s', '%s', '%s', '%s', '%d')
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    public static function get($id) {
        global $wpdb;
        $table = self::get_table_name();
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
    }
    
    public static function get_all($status = null, $limit = 100, $offset = 0) {
        global $wpdb;
        $table = self::get_table_name();
        
        if ($status !== null) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE status = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $status,
                $limit,
                $offset
            ));
        } else {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            ));
        }
    }
    
    public static function get_approved($limit = 100) {
        return self::get_all(1, $limit, 0);
    }
    
    public static function get_with_details($id) {
        global $wpdb;
        $table_scenes = self::get_table_name();
        $table_movies = $wpdb->prefix . 'skate_movies';
        $table_spots = $wpdb->prefix . 'skate_spots';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT s.*, m.title as movie_title, sp.title as spot_title 
            FROM $table_scenes s
            LEFT JOIN $table_movies m ON s.movie_id = m.id
            LEFT JOIN $table_spots sp ON s.spot_id = sp.id
            WHERE s.id = %d",
            $id
        ));
    }
    
    public static function get_all_with_details($status = null) {
        global $wpdb;
        $table_scenes = self::get_table_name();
        $table_movies = $wpdb->prefix . 'skate_movies';
        $table_spots = $wpdb->prefix . 'skate_spots';
        
        $where = $status !== null ? $wpdb->prepare("WHERE s.status = %d", $status) : "";
        
        return $wpdb->get_results(
            "SELECT s.*, m.title as movie_title, sp.title as spot_title 
            FROM $table_scenes s
            LEFT JOIN $table_movies m ON s.movie_id = m.id
            LEFT JOIN $table_spots sp ON s.spot_id = sp.id
            $where
            ORDER BY s.created_at DESC"
        );
    }
    
    public static function update($id, $data) {
        global $wpdb;
        $table = self::get_table_name();
        
        $result = $wpdb->update(
            $table,
            $data,
            array('id' => $id),
            null,
            array('%d')
        );
        
        return $result !== false;
    }
    
    public static function delete($id) {
        global $wpdb;
        $table = self::get_table_name();
        
        // Also delete related skater-scene links
        $table_links = $wpdb->prefix . 'skate_skater_scenes';
        $wpdb->delete($table_links, array('scene_id' => $id), array('%d'));
        
        return $wpdb->delete(
            $table,
            array('id' => $id),
            array('%d')
        );
    }
    
    public static function update_status($id, $status) {
        return self::update($id, array('status' => $status));
    }
    
    public static function count($status = null) {
        global $wpdb;
        $table = self::get_table_name();
        
        if ($status !== null) {
            return $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE status = %d",
                $status
            ));
        } else {
            return $wpdb->get_var("SELECT COUNT(*) FROM $table");
        }
    }
    
    public static function add_skater($scene_id, $skater_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'skate_skater_scenes';
        
        return $wpdb->insert(
            $table,
            array('scene_id' => $scene_id, 'skater_id' => $skater_id),
            array('%d', '%d')
        );
    }
    
    public static function remove_skater($scene_id, $skater_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'skate_skater_scenes';
        
        return $wpdb->delete(
            $table,
            array('scene_id' => $scene_id, 'skater_id' => $skater_id),
            array('%d', '%d')
        );
    }
    
    public static function get_skaters($scene_id) {
        global $wpdb;
        $table_links = $wpdb->prefix . 'skate_skater_scenes';
        $table_skaters = $wpdb->prefix . 'skate_skaters';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT sk.* FROM $table_skaters sk
            INNER JOIN $table_links ssl ON sk.id = ssl.skater_id
            WHERE ssl.scene_id = %d",
            $scene_id
        ));
    }
}
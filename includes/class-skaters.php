<?php
/**
 * Skaters CRUD operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Skate_Skaters {
    
    private static $table_name = 'skate_skaters';
    
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::$table_name;
    }
    
    public static function create($data) {
        global $wpdb;
        
        $table = self::get_table_name();
        
        $defaults = array(
            'name' => '',
            'bio' => '',
            'social_url' => '',
            'status' => 0
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $wpdb->insert(
            $table,
            $data,
            array('%s', '%s', '%s', '%d')
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
                "SELECT * FROM $table WHERE status = %d ORDER BY name ASC LIMIT %d OFFSET %d",
                $status,
                $limit,
                $offset
            ));
        } else {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table ORDER BY name ASC LIMIT %d OFFSET %d",
                $limit,
                $offset
            ));
        }
    }
    
    public static function get_approved($limit = 100) {
        return self::get_all(1, $limit, 0);
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
        $wpdb->delete($table_links, array('skater_id' => $id), array('%d'));
        
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
    
    public static function get_scenes($skater_id) {
        global $wpdb;
        $table_links = $wpdb->prefix . 'skate_skater_scenes';
        $table_scenes = $wpdb->prefix . 'skate_scenes';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT sc.* FROM $table_scenes sc
            INNER JOIN $table_links ssl ON sc.id = ssl.scene_id
            WHERE ssl.skater_id = %d",
            $skater_id
        ));
    }
}
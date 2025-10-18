<?php
/**
 * Spots CRUD operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Skate_Spots {
    
    private static $table_name = 'skate_spots';
    
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::$table_name;
    }
    
    public static function create($data) {
        global $wpdb;
        
        $table = self::get_table_name();
        
        $defaults = array(
            'title' => '',
            'description' => '',
            'latitude' => null,
            'longitude' => null,
            'address' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
            'country' => '',
            'spot_type' => '',
            'image_url' => '',
            'status' => 0
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $wpdb->insert(
            $table,
            $data,
            array('%s', '%s', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d')
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
}
<?php

/**
 * @package sync
 * @version 1.0
 */
/*
  Plugin Name: sync-post
  Plugin URI: http://wordpress.org/plugins/sync_post/
  Description: sync between different zones through RabbitMQ
  Version: 1.0
  Author: Neuedu
  Author URI: http://ma.tt/
 */
require_once('moudle.php');

function after_insert_post($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();
    try {
        $result = mysql_query("SELECT * FROM wp_posts where ID =" . $para1);
        $row = mysql_fetch_array($result);
    } 
    catch (Exception $e) {
        error_log("there has an error occurred to database during function " . __FUNCTION__);
    }
    $sync = array(
        'type' => 'insert',
        'table' => 'wp_posts',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json, 'post');
}

function after_save_post($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();
    try {
        $result = mysql_query("SELECT * FROM wp_posts where ID =" . $para1);
        $row = mysql_fetch_array($result);
    } 
    catch (Exception $e) {
        error_log("there has an error occurred to database during function " . __FUNCTION__);
    }
    $sync = array(
        'type' => 'insert',
        'table' => 'wp_posts',
        'data' => $row
    );
    $sync_json = json_encode($sync);
    push_MQ($sync_json, 'post');
}

function after_del_post($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();
    try {
        $result = mysql_query("SELECT * FROM wp_posts where ID =" . $para1);
        $row = mysql_fetch_array($result);
    } 
    catch (Exception $e) {
        error_log("there has an error occurred to database during function " . __FUNCTION__);
    }
    $sync = array(
        'type' => 'delete',
        'table' => 'wp_posts',
        'data' => $row
    );
    $sync_json = json_encode($sync);
    push_MQ($sync_json, 'post');
}

function after_trash_post($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();
    try {
        $result = mysql_query("SELECT * FROM wp_posts where ID =" . $para1);

        $row = mysql_fetch_array($result);
    } 
    catch (Exception $e) {
        error_log("there has an error occurred to database during function " . __FUNCTION__);
    }
    $sync = array(
        'type' => 'trash',
        'table' => 'wp_posts',
        'data' => $row
    );
    $sync_json = json_encode($sync);
    push_MQ($sync_json, 'post');
}

function after_publish_post($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();
    try {
        $result = mysql_query("SELECT * FROM wp_posts where ID =" . $para1);
        $row = mysql_fetch_array($result);
    } 
    catch (Exception $e) {
        error_log("there has an error occurred to database during function " . __FUNCTION__);
    }
    $sync = array(
        'type' => 'untrash',
        'table' => 'wp_posts',
        'data' => $row
    );
    $sync_json = json_encode($sync);
    push_MQ($sync_json, 'post');
}

function after_post_meta($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();
    try {
        $query = "SELECT * FROM wp_postmeta WHERE post_id = " . $para1 . " AND meta_key = '" . $para2 . "'";
        $result = mysql_query($query);
        while ($row = mysql_fetch_array($result)) {
            $sync = array(
                'type' => 'insert',
                'table' => 'wp_postmeta',
                'data' => $row
            );
            $sync_json = json_encode($sync);
            push_MQ($sync_json, 'post');
        }
    } 
    catch (Exception $e) {
        error_log("there has an error occurred to database during function " . __FUNCTION__);
    }
}

function after_update_post_meta($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();
    try {
        $query = "SELECT * FROM wp_postmeta WHERE post_id = " . $para1 . " AND meta_key = '" . $para2 . "'";
        $result = mysql_query($query);
        while ($row = mysql_fetch_array($result)) {
            $sync = array(
                'type' => 'update',
                'table' => 'wp_postmeta',
                'data' => $row
            );
            $sync_json = json_encode($sync);
            push_MQ($sync_json, 'post');
        }
    } 
    catch (Exception $e) {
        error_log("there has an error occurred to database during function " . __FUNCTION__);
    }
}

add_action('wp_insert_post', after_insert_post);
add_action('save_post', after_save_post);
add_action('delete_post', after_del_post);
add_action('wp_trash_post', after_trash_post);
add_action('untrash_post', after_publish_post);
add_action('add_post_meta', after_post_meta, 5, 2);
add_action('update_post_meta', after_update_post_meta, 5, 2);



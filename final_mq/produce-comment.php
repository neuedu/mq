<?php

/**
 * @package sync
 * @version 1.0
 */
/*
  Plugin Name: sync-comment1
  Plugin URI: http://wordpress.org/plugins/sync_post/
  Description: sync between different zones through RabbitMQ
  Version: 1.0
  Author: Neuedu
  Author URI: http://ma.tt/
 */

require_once('moudle.php');

function after_insert_comment($para1 = "", $para2 = "", $para3 = "") {

    $config = ini_init();

    mysql_init();
    try {
        $result = mysql_query("SELECT * FROM wp_comments where comment_ID =" . $para1);

        $row = mysql_fetch_array($result);
    } catch (Exception $e) {
        echo "there has an error occurred to database during function " . __FUNCTION__;
    }

    $sync = array(
        'type' => 'insert',
        'table' => 'wp_comments',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json, 'comment');
}

function after_trash_com($para1 = "", $para2 = "", $para3 = "") {

    $config = ini_init();

    mysql_init();
    try {
        $result = mysql_query("SELECT * FROM wp_comments where comment_ID = " . $para1);

        $row = mysql_fetch_array($result);
    } catch (Exception $e) {
        echo "there has an error occurred to database during function " . __FUNCTION__;
    }
    $sync = array(
        'type' => 'trash',
        'table' => 'wp_comments',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json, 'comment');
}

function after_untrash_com($para1 = "", $para2 = "", $para3 = "") {

    $config = ini_init();

    mysql_init();
    try {
        $result = mysql_query("SELECT * FROM wp_comments where comment_ID = " . $para1);

        $row = mysql_fetch_array($result);
    } catch (Exception $e) {
        echo "there has an error occurred to database during function " . __FUNCTION__;
    }
    $sync = array(
        'type' => 'untrash',
        'table' => 'wp_comments',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json, 'comment');
}

function after_del_com($para1 = "", $para2 = "", $para3 = "") {

    $config = ini_init();

    mysql_init();
    try {
        $result = mysql_query("SELECT * FROM wp_comments where comment_ID = " . $para1);

        $row = mysql_fetch_array($result);
    } catch (Exception $e) {
        echo "there has an error occurred to database during function " . __FUNCTION__;
    }
    $sync = array(
        'type' => 'delete',
        'table' => 'wp_comments',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json, 'comment');
}

function after_comment_meta($para1 = "", $para2 = "", $para3 = "") {

    $config = ini_init();

    mysql_init();
    try {
        $result = mysql_query("SELECT * FROM wp_commentmeta where comment_id = " . $para1);

        while ($row = mysql_fetch_array($result)) {
            $sync = array(
                'type' => 'insert',
                'table' => 'wp_commentmeta',
                'data' => $row
            );
            $sync_json = json_encode($sync);
            push_MQ($sync_json, 'comment');
        }
    } catch (Exception $e) {
        echo "there has an error occurred to database during function " . __FUNCTION__;
    }
}

add_action('wp_insert_comment', after_insert_comment);

add_action('trash_comment', after_trash_com);

add_action('untrash_comment', after_untrash_com);

add_action('delete_comment', after_del_com);

add_action('add_comment_meta', after_comment_meta);

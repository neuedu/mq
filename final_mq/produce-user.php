<?php

/**
 * @package sync
 * @version 1.0
 */
/*
  Plugin Name: sync-user
  Plugin URI: http://wordpress.org/plugins/sync_post/
  Description: sync between different zones through RabbitMQ
  Version: 1.0
  Author: Neuedu
  Author URI: http://ma.tt/
 */
require_once('moudle.php');

function after_update_userprofile($para1 = "", $para2 = "", $para3 = "") {

    $config = ini_init();

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_users where ID =" . $para1);

    $row = mysql_fetch_array($result);

    $sync = array(
        'type' => 'update',
        'table' => 'wp_users',
        'data' => $row
    );

    $sync_json = json_encode($sync);

    push_MQ($sync_json, 'user');
    $result = mysql_query("SELECT * FROM wp_usermeta where user_id = " . $para1);

    while ($row = mysql_fetch_array($result)) {
        $sync = array(
            'type' => 'update_usermeta',
            'table' => 'wp_usermeta',
            'data' => $row
        );
        $sync_json = json_encode($sync);
        push_MQ($sync_json, 'user');
    }
}

function after_update_register($para1 = "", $para2 = "", $para3 = "") {

    $config = ini_init();

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_users where ID =" . $para1);

    $row = mysql_fetch_array($result);

    $sync = array(
        'type' => 'insert',
        'table' => 'wp_users',
        'data' => $row
    );

    $sync_json = json_encode($sync);

    push_MQ($sync_json, 'user');
}

function after_del_user($para1 = "", $para2 = "", $para3 = "") {

    $config = ini_init();

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_users where ID =" . $para1);

    $row = mysql_fetch_array($result);

    $sync = array(
        'type' => 'user_delete',
        'table' => 'wp_users',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json, 'user');
}

function after_user_meta($para1 = "", $para2 = "", $para3 = "") {

    $config = ini_init();

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_usermeta where user_id = " . $para1);

    while ($row = mysql_fetch_array($result)) {
        $sync = array(
            'type' => 'insert',
            'table' => 'wp_usermeta',
            'data' => $row
        );
        $sync_json = json_encode($sync);
        push_MQ($sync_json, 'user');
    }
}

add_action('profile_update', after_update_userprofile);

add_action('user_register', after_update_register);

add_action('delete_user', after_del_user);

add_action('add_user_meta', after_user_meta);

<?php

/**
 * @package sync
 * @version 1.0
 */
/*
  Plugin Name: sync
  Plugin URI: http://wordpress.org/plugins/sync_post/
  Description: sync between different zones through RabbitMQ
  Version: 1.0
  Author: Neuedu
  Author URI: http://ma.tt/
 */

function after_update_userprofile($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_users where ID =" . $para1);

    $row = mysql_fetch_array($result);

    $sync = array(
        'type' => 'update',
        'table' => 'wp_users',
        'data' => $row
    );

    $sync_json = json_encode($sync);

    push_MQ($sync_json);
    $result = mysql_query("SELECT * FROM wp_usermeta where user_id = " . $para1);

    while ($row = mysql_fetch_array($result)) {
        $sync = array(
            'type' => 'update_usermeta',
            'table' => 'wp_usermeta',
            'data' => $row
        );
        //var_dump($row);
        $sync_json = json_encode($sync);
        push_MQ($sync_json);
    }
}

function after_insert_comment($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_comments where comment_ID =" . $para1);

    $row = mysql_fetch_array($result);

    $sync = array(
        'type' => 'insert',
        'table' => 'wp_comments',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json);
}

function after_update_register($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_users where ID =" . $para1);

    $row = mysql_fetch_array($result);

    $sync = array(
        'type' => 'insert',
        'table' => 'wp_users',
        'data' => $row
    );

    $sync_json = json_encode($sync);

    push_MQ($sync_json);
}

function after_insert_post($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_posts where ID =" . $para1);

    $row = mysql_fetch_array($result);

    $sync = array(
        'type' => 'insert',
        'table' => 'wp_posts',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json);
}

function after_save_post($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_posts where ID =" . $para1);

    $row = mysql_fetch_array($result);

    $sync = array(
        'type' => 'insert',
        'table' => 'wp_posts',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json);
}

function after_del_post($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_posts where ID =" . $para1);

    $row = mysql_fetch_array($result);

    $sync = array(
        'type' => 'delete',
        'table' => 'wp_posts',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json);
}

function after_trash_post($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_posts where ID =" . $para1);

    $row = mysql_fetch_array($result);

    $sync = array(
        'type' => 'trash',
        'table' => 'wp_posts',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json);
}

function after_publish_post($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_posts where ID =" . $para1);

    $row = mysql_fetch_array($result);

    $sync = array(
        'type' => 'untrash',
        'table' => 'wp_posts',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json);
}

function after_del_user($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_users where ID =" . $para1);

    $row = mysql_fetch_array($result);

    $sync = array(
        'type' => 'user_delete',
        'table' => 'wp_users',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json);
    //exit;
}

function after_user_meta($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_usermeta where user_id = " . $para1);

    while ($row = mysql_fetch_array($result)) {
        $sync = array(
            'type' => 'insert',
            'table' => 'wp_usermeta',
            'data' => $row
        );
        //var_dump($row);
        $sync_json = json_encode($sync);
        push_MQ($sync_json);
    }
}

function after_trash_com($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_comments where comment_ID = " . $para1);

    $row = mysql_fetch_array($result);

    $sync = array(
        'type' => 'com_trash',
        'table' => 'wp_comments',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json);
}

function after_untrash_com($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_comments where comment_ID = " . $para1);

    $row = mysql_fetch_array($result);

    $sync = array(
        'type' => 'com_untrash',
        'table' => 'wp_comments',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json);
}

function after_del_com($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_comments where comment_ID = " . $para1);

    $row = mysql_fetch_array($result);

    $sync = array(
        'type' => 'com_delete',
        'table' => 'wp_comments',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json);
}

function after_post_meta($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_postmeta where post_id = " . $para1);

    while ($row = mysql_fetch_array($result)) {
        $sync = array(
            'type' => 'insert',
            'table' => 'wp_postmeta',
            'data' => $row
        );
        //var_dump($row);
        $sync_json = json_encode($sync);
        push_MQ($sync_json);
    }
    //exit;
}

function after_comment_meta($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_commentmeta where comment_id = " . $para1);

    while ($row = mysql_fetch_array($result)) {
        $sync = array(
            'type' => 'insert',
            'table' => 'wp_commentmeta',
            'data' => $row
        );
        //var_dump($row);
        $sync_json = json_encode($sync);
        push_MQ($sync_json);
    }
}

function after_update_post_meta($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();

    $result = mysql_query("SELECT * FROM wp_postmeta where post_id = " . $para1);

    while ($row = mysql_fetch_array($result)) {
        $sync = array(
            'type' => 'update',
            'table' => 'wp_postmeta',
            'data' => $row
        );
        //var_dump($row);
        $sync_json = json_encode($sync);
        push_MQ($sync_json);
    }
    //exit;
}

/*
  function after_update_user_meta($para1 = "", $para2 = "", $para3 = "") {

  mysql_init();

  $result = mysql_query("SELECT * FROM wp_usermeta where user_id = " . $para1);

  while ($row = mysql_fetch_array($result)) {
  $sync = array(
  'type' => 'update',
  'table' => 'wp_usermeta',
  'data' => $row
  );
  //var_dump($row);
  $sync_json = json_encode($sync);
  push_MQ($sync_json);
  }
  //exit;
  }
 */

add_action('profile_update', after_update_userprofile);

add_action('user_register', after_update_register);

add_action('wp_insert_comment', after_insert_comment);

add_action('wp_insert_post', after_insert_post);

add_action('save_post', after_save_post);

add_action('delete_post', after_del_post);

add_action('wp_trash_post', after_trash_post);

add_action('untrash_post', after_publish_post);

add_action('delete_user', after_del_user);

add_action('trash_comment', after_trash_com);

add_action('untrash_comment', after_untrash_com);

add_action('delete_comment', after_del_com);

add_action('add_user_meta', after_user_meta);

add_action('add_post_meta', after_post_meta);

add_action('update_post_meta', after_update_post_meta);

add_action('add_comment_meta', after_comment_meta);

function push_MQ($message) {
    $conn_args = array(
        'host' => '192.168.181.14',
        //'host' => '127.0.0.1',
        'port' => '5672',
        'login' => 'guest',
        'password' => 'guest',
        'vhost' => '/'
    );
    $e_name = 'chytest';

    $k_route = 'key_1';

    $conn = new AMQPConnection($conn_args);
    if (!$conn->connect()) {
        die("Cannot connect to the broker!\n");
    }
    $channel = new AMQPChannel($conn);

    $ex = new AMQPExchange($channel);
    $ex->setName($e_name);

    echo "Send Message:" . $ex->publish($message, $k_route) . "\n";

    $conn->disconnect();
}

function mysql_init() {
    $con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
    if (!$con) {
        die('Could not connect: ' . mysql_error());
    }
    mysql_select_db(DB_NAME, $con);
}

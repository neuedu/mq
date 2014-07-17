<?php

/**
 * @package sync123
 * @version 1.0
 */
/*
  Plugin Name: sync-comment
  Plugin URI: http://wordpress.org/plugins/sync_post/
  Description: sync between different zones through RabbitMQ
  Version: 1.0
  Author: Neuedu
  Author URI: http://ma.tt/
 */
function after_insert_comment($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();
    try {
        $result = mysql_query("SELECT * FROM wp_comments where comment_ID =" . $para1);

        $row = mysql_fetch_array($result);
    } catch (Exception $e) {
        echo "there has an error occurred to database! : after_insert_comment";
    }
    $sync = array(
        'type' => 'insert',
        'table' => 'wp_comments',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json);
}

function after_trash_com($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();
    try {
        $result = mysql_query("SELECT * FROM wp_comments where comment_ID = " . $para1);

        $row = mysql_fetch_array($result);
    } catch (Exception $e) {
        echo "there has an error occurred to database! : after_trash_com";
    }
    $sync = array(
        'type' => 'trash',
        'table' => 'wp_comments',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json);
}

function after_untrash_com($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();
    try {
        $result = mysql_query("SELECT * FROM wp_comments where comment_ID = " . $para1);

        $row = mysql_fetch_array($result);
    } catch (Exception $e) {
        echo "there has an error occurred to database! : after_untrash_com";
    }
    $sync = array(
        'type' => 'untrash',
        'table' => 'wp_comments',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json);
}

function after_del_com($para1 = "", $para2 = "", $para3 = "") {

    mysql_init();
    try {
        $result = mysql_query("SELECT * FROM wp_comments where comment_ID = " . $para1);

        $row = mysql_fetch_array($result);
    } catch (Exception $e) {
        echo "there has an error occurred to database! : after_del_com";
    }
    $sync = array(
        'type' => 'delete',
        'table' => 'wp_comments',
        'data' => $row
    );

    $sync_json = json_encode($sync);
    push_MQ($sync_json);
}

add_action('wp_insert_comment', after_insert_comment);

add_action('trash_comment', after_trash_com);

add_action('untrash_comment', after_untrash_com);

add_action('delete_comment', after_del_com);

function push_MQ($message) {
    $conf = ini_init();
    $conn_args = array(
        //'host' => '192.168.181.14',
        'host' => $conf['phost'],
        'port' => $conf['pport'],
        'login' => $conf['plogin'],
        'password' => $conf['ppassword'],
        'vhost' => $conf['pvhost']
    );

    $e_name = $conf['pe_Cname'];

    $k_route = $conf['pk_Croute'];
    try {
        $conn = new AMQPConnection($conn_args);
    } catch (Exception $e) {
        echo "there has an error occurred to database! : new AMPQConnection";
    }
    if (!$conn->connect()) {
        die("Cannot connect to the broker!\n");
    }
    try {
        $channel = new AMQPChannel($conn);
    } catch (Exception $e) {
        echo "there has an error occurred to database! : new AMPQChannel";
    }
    try {
        $ex = new AMQPExchange($channel);
    } catch (Exception $e) {
        echo "there has an error occurred to database! : new AMPQExchange";
    }
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

function ini_init() {
    $ini_array = parse_ini_file("mqconfig.ini");
    return $ini_array;
}


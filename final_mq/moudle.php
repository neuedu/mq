<?php

function push_MQ($message, $type) {
    $config = ini_init();
    $con_type = '';
    switch ($type) {
        case 'post':
            $con_type = 'MQ-post';
            break;
        case 'user':
            $con_type = 'MQ-user';
            break;
        case 'comment':
            $con_type = 'MQ-comment';
            break;
        default:
            error_log("no config type!");
            mqexception($message);
            break;
    }

    $conn_args = array(
        'host' => $config[$con_type]['host'],
        'port' => $config[$con_type]['port'],
        'login' => $config[$con_type]['login'],
        'password' => $config[$con_type]['password'],
        'vhost' => $config[$con_type]['vhost']
    );

    $e_name = $config[$con_type]['ex_name'];

    $k_route = $config[$con_type]['k_route'];
    try {
        $conn = new AMQPConnection($conn_args);
    } 
    catch (Exception $e) {
        error_log("there has en error occurred to AMQPConnection!!");
        mqexception($message);
    }
    if (!$conn->connect()) {
        die("Cannot connect to the broker!\n");
    }
    try {
        $channel = new AMQPChannel($conn);
    } 
    catch (Exception $e) {
        error_log("there has en error occurred to AMQPChannel!!");
        mqexception($message);
    }
    $ex = new AMQPExchange($channel);
    $ex->setName($e_name);
    
    try {
        $ex->publish($message, $k_route);
    } 
    catch (Exception $e) {
        error_log("there has en error occurred to sendmessage!!");
        mqexception($message);
    }
    $conn->disconnect();
    return '1';
}

function mysql_init() {
    $con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
    if (!$con) {
        die('Could not connect: ' . mysql_error());
    }
    mysql_select_db(DB_NAME, $con);
}

function ini_init() {
    $ini_array = parse_ini_file("config.ini", true);
    return $ini_array;
}

function mqexception($message) {
    mysql_init();
    $mysql_insert = 'insert into wp_mqexception (`id`, `message`, `type`, `if_sync`) values ("", "' . base64_encode($message) . '", "' . $type . '", "0");';
    error_log($mysql_insert);
    mysql_query($mysql_insert);
}

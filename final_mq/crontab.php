<?php

require_once('moudle.php');
require_once(dirname(__FILE__) . '/../../wp-load.php');
$conf = ini_init();

$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$con) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db(DB_NAME, $con);


$result = mysql_query("SELECT * FROM wp_mqexception where if_sync = 0;");

while ($row = mysql_fetch_array($result)) {
    $id = $row['id'];
    $type = $row['type'];
    $message = base64_decode($row['message']);
    $flag = push_MQ($message, $type);
    if ($flag == '1') {
        mysql_query("UPDATE wp_mqexception set `if_sync` = '1' where id = '" . $id . "';");
    } else {
        continue;
    }
}

echo 'finish';



<?php

$conn_args = array(
    'host' => '127.0.0.1',
    'port' => '5672',
    'login' => 'guest',
    'password' => 'guest',
    'vhost' => '/'
);
$e_name = 'chytest';
$q_name = 'q_linvo';
$k_route = 'key_1';
//databases init
$con = mysql_connect("localhost", "root", "epals");
if (!$con) {
    die('Could not connect: ' . mysql_error());
}
try {
    mysql_select_db("wordpress_my", $con);
} catch (Exception $e) {
    echo 'databases select failed!';
}
$conn = new AMQPConnection($conn_args);
if (!$conn->connect()) {
    die("Cannot connect to the broker!\n");
}
$channel = new AMQPChannel($conn);

$ex = new AMQPExchange($channel);
$ex->setName($e_name);
$ex->setType(AMQP_EX_TYPE_DIRECT);
$ex->setFlags(AMQP_DURABLE);
echo "Exchange Status:" . $ex->declare() . "\n";

$q = new AMQPQueue($channel);
$q->setName($q_name);
$q->setFlags(AMQP_DURABLE);
echo "Message Total:" . $q->declare() . "\n";

echo 'Queue Bind: ' . $q->bind($e_name, $k_route) . "\n";

echo "Message:\n";
while (True) {
    $q->consume('insertData');
    //$q->consume('processMessage');   
    //$q->consume('processMessage', AMQP_AUTOACK); 
}
$conn->disconnect();

function insertData($envelope, $queue) {
    $msg = $envelope->getBody();

    $sync_get = json_decode($msg);
    var_dump($sync_get);
    $row_get = object_to_array($sync_get->data);
    $type = $sync_get->type;
    var_dump($type);
    $table = $sync_get->table;
    $data_k = array();
    $data_v = array();
    $id = $row_get[ID];
    if (!$row_get[ID]) {
        $id = $row_get[comment_ID];
    }
    $i = 0;
    foreach ($row_get as $key => $v) {
        if (is_numeric($key)) {
            continue;
        }

        $data_k[$i] = $key;
        $data_v[$i] = $v;
        $i++;
    }
    if ($type == 'insert') {

        $result = mysql_query("SELECT * FROM " . $table . " where " . $data_k[0] . " = " . $data_v[0]);
        $row = mysql_fetch_array($result);
        var_dump($row);
        if ($row) {
            $type = 'update';
        }
    }
    if ($type == 'update') {
        $length = count($data_k);
        $update_data = '';
        for ($i = 0; $i < $length; $i++) {
            $update_data = $update_data . '`' . $data_k[$i] . '`="' . $data_v[$i] . '",';
        }
        $update_data = substr($update_data, 0, -1);
        $mysql_update = 'update ' . $table . ' set ' . $update_data . ' where id = ' . $id . ';';
        var_dump($mysql_update);
        mysql_query($mysql_update);
        echo '<br>';
    } else if ($type == 'insert') {
        $mysql_insert_key = '';
        foreach ($data_k as $v) {
            $mysql_insert_key = $mysql_insert_key . '`' . $v . '`' . ',';
        }
        $mysql_insert_key = substr($mysql_insert_key, 0, -1);

        $mysql_insert_val = '';
        foreach ($data_v as $v) {
            $mysql_insert_val = $mysql_insert_val . '"' . $v . '"' . ',';
        }
        $mysql_insert_val = substr($mysql_insert_val, 0, -1);

        $mysql_insert = 'insert into ' . $table . ' (' . $mysql_insert_key . ') values (' . $mysql_insert_val . ');';
        var_dump($mysql_insert);
        mysql_query($mysql_insert);
    } else if ($type == 'delete') {
        $mysql_delete = 'delete from ' . $table . ' where ID = "' . $id . '";';
        var_dump($mysql_delete);
        mysql_query($mysql_delete);
    } else if ($type == 'trash') {
        $mysql_update = 'update ' . $table . ' set `post_status` = "trash" where id = "' . $id . '";';
        var_dump($mysql_update);
        mysql_query($mysql_update);
    } else if ($type == 'untrash') {
        $mysql_update = 'update ' . $table . ' set `post_status` = "publish" where id = "' . $id . '";';
        var_dump($mysql_update);
        mysql_query($mysql_update);
    } else if ($type == 'com_trash') {
        $mysql_update = 'update ' . $table . ' set `comment_approved` = "trash" where comment_ID = "' . $id . '";';
        var_dump($mysql_update);
        mysql_query($mysql_update);
    } else if ($type == 'com_untrash') {
        $mysql_update = 'update ' . $table . ' set `comment_approved` = "1" where comment_ID = "' . $id . '";';
        var_dump($mysql_update);
        mysql_query($mysql_update);
    } else if ($type == 'com_delete') {
        $mysql_update = 'delete from ' . $table . ' where `comment_ID` = "' . $id . '";';
        var_dump($mysql_update);
        mysql_query($mysql_update);
    } else if ($type == 'user_delete') {
        $mysql_delete = 'delete from ' . $table . ' where ID = "' . $id . '";';
        $mysql_delete_um = 'delete from wp_usermeta where user_id = "' . $id . '";';
        var_dump($mysql_delete);
        var_dump($mysql_delete_um);
        mysql_query($mysql_delete);
        mysql_query($mysql_delete_um);
    } else if ($type == 'update_usermeta') {
        $length = count($data_k);
        $update_data = '';
        for ($i = 0; $i < $length; $i++) {
            $update_data = $update_data . '`' . $data_k[$i] . '`="' . $data_v[$i] . '",';
        }
        $update_data = substr($update_data, 0, -1);
        var_dump($update_data);
        $id = $data_v[0];
        $mysql_update = 'update ' . $table . ' set ' . $update_data . ' where umeta_id = "' . $id . '";';
        var_dump($mysql_update);
        mysql_query($mysql_update);
    }
    $queue->ack($envelope->getDeliveryTag());
}

function object_to_array($obj) {
    $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
    foreach ($_arr as $key => $val) {
        $val = (is_array($val)) || is_object($val) ? object_to_array($val) : $val;
        $arr[$key] = $val;
    }
    return $arr;
}

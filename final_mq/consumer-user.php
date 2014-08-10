<?php

$type = "CMQ-user";
require_once('moudle.php');
require_once(dirname(dirname(dirname(__DIR__))) . '/wp-load.php');
$conf = ini_init();
$conn_args = array(
    'host' => $conf[$type]['host'],
    'port' => $conf[$type]['port'],
    'login' => $conf[$type]['login'],
    'password' => $conf[$type]['password'],
    'vhost' => $conf[$type]['vhost']
);
$e_name = $conf[$type]['ex_name'];
$q_name = $conf[$type]['q_name'];
$k_route = $conf[$type]['k_route'];
//databases init
$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$con) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db(DB_NAME, $con);
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
$ex->setType(AMQP_EX_TYPE_DIRECT);
$ex->setFlags(AMQP_DURABLE);
echo "Exchange Status:" . $ex->declare() . "\n";
try {
    $q = new AMQPQueue($channel);
} catch (Exception $e) {
    echo "there has an error occurred to database! : new AMQPQueue";
}
$q->setName($q_name);
$q->setFlags(AMQP_DURABLE);
echo "Message Total:" . $q->declare() . "\n";

echo 'Queue Bind: ' . $q->bind($e_name, $k_route) . "\n";

echo "Message:\n";
while (True) {
    $q->consume('insertData');
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
    var_dump($id);
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
            $data_key = mysql_real_escape_string($data_k[$i]);
            $data_value = mysql_real_escape_string($data_v[$i]);
            $update_data = $update_data . '`' . $data_key . '`="' . $data_value . '",';
        }
        $update_data = substr($update_data, 0, -1);
        $mysql_update = 'update ' . $table . ' set ' . $update_data . ' where `' . $data_k[0] . '` = ' . $data_v[0] . ';';
        var_dump($mysql_update);
        mysql_query($mysql_update);
        echo '<br>';
    } else if ($type == 'insert') {
        $mysql_insert_key = '';
        foreach ($data_k as $v) {
            $v = mysql_real_escape_string($v);
            $mysql_insert_key = $mysql_insert_key . '`' . $v . '`' . ',';
        }
        $mysql_insert_key = substr($mysql_insert_key, 0, -1);

        $mysql_insert_val = '';
        foreach ($data_v as $v) {
            $v = mysql_real_escape_string($v);
            $mysql_insert_val = $mysql_insert_val . '"' . $v . '"' . ',';
        }
        $mysql_insert_val = substr($mysql_insert_val, 0, -1);

        $mysql_insert = 'insert into ' . $table . ' (' . $mysql_insert_key . ') values (' . $mysql_insert_val . ');';
        var_dump($mysql_insert);
        mysql_query($mysql_insert);
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
        $mysql_update = 'update ' . $table . ' set ' . $update_data . ' where umeta_id = "' . $data_v[0] . '";';
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

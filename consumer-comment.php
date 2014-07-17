<?php

require_once(dirname(__FILE__) . '/../../wp-load.php');
$conf = inic_init();
$conn_args = array(
    'host' => $conf['chost'],
    'port' => $conf['cport'],
    'login' => $conf['clogin'],
    'password' => $conf['cpassword'],
    'vhost' => $conf['cvhost']
);
$e_name = $conf['ce_Cname'];
$q_name = $conf['cq_Cname'];
;
$k_route = $conf['ck_Croute'];
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
    //var_dump($type);
    $table = $sync_get->table;
    $data_k = array();
    $data_v = array();
    $id = $row_get[comment_ID];
    //var_dump($id);
    $i = 0;
    foreach ($row_get as $key => $v) {
        if (is_numeric($key)) {
            unset($row_get[$key]);
            continue;
        }

        $data_k[$i] = $key;
        $data_v[$i] = $v;
        $i++;
    }
    if ($type == 'insert') {
        $result = mysql_query("SELECT * FROM wp_comments where comment_ID =" . $row_get['comment_ID']);

        $row = mysql_fetch_array($result);
        if ($row) {
            var_dump("data has been synced!");
        } else {
            unset($row_get['comment_ID']);
            var_dump('do insert action++++');
        }
        //wp_insert_comment($row_get);
    } else if ($type == 'trash') {
        var_dump('do trash action++++');
        var_dump($id);
        wp_set_comment_status($id, 'trash', $wp_error = false);
    } else if ($type == 'untrash') {
        var_dump('do untrash action++++');
        var_dump($id);
        wp_set_comment_status($id, '1', $wp_error = false);
    } else if ($type == 'delete') {
        var_dump('do delete action++++');
        var_dump($id);
        wp_delete_comment($id, $force_delete = false);
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

function inic_init() {
    $ini_array = parse_ini_file("mqconfig.ini");
    return $ini_array;
}


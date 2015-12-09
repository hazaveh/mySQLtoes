<?php
/* Author: Mahdi Hazaveh <mahdi@hazaveh.net> */

// Specify MySQL database name to connect:
define('DB_NAME', 'mysqltoes');
// Specify Database server hostname
define('DB_HOST', '192.168.5.49');
// Specify MySQL database username
define('DB_USER', 'root');
// Specify MySQL database password
define('DB_PASS', 'mahdi');
// Specify TableName To be imported from MySQL
define('DB_TABLE', '');
// Specify MySQL COL to be Considered as ES _id Column.
define('DB_COL', 'id');
// Specify MySQL Server Port, Only change if the Default Port is not 3306
define('DB_PORT', '3306');
// Specify Elasticsearch Server Hostname
$ES_HOST = array('192.168.5.49:9200');
// Specify Elasticsearch index name.
define('ES_INDEX', 'mysqltoes');
// dividing large select * into:
define('Q_LIMIT', 500);
// Specify ES Type:
define('ES_TYPE', 'mhd');
// Should MySQLtoes use a table column as id column in Elasticsearch?
define('USE_DB_COLUMN', FALSE);




?>

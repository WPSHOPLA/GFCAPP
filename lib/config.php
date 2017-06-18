<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '-1');
/* DATABASE CONFIGURATION */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'postgres');
define('DB_PASSWORD', 'youaremine');
define('DB_DATABASE', 'gfc');
define("BASE_URL", "http://localhost/gfc/");
define("ROOT_DIR", 'C:/xampp/htdocs/gfc' );

define("NEED_TO_SUBSCRIBE", "need_to_subscribe");
define("NEED_TO_UPDATE_SUBSCRIBE", "need_to_update_subscribe");
define("STAFF_ACCOUNT", "staff");


function getDB() {
	$dbhost = DB_SERVER;
	$dbuser = DB_USERNAME;
	$dbpass = DB_PASSWORD;
	$dbname = DB_DATABASE;
	try {
		$dbConnection = new PDO("pgsql:host=$dbhost;port=5432;dbname=$dbname", $dbuser, $dbpass);
		$dbConnection->exec("set names utf8");
		$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $dbConnection;
	} catch (PDOException $e) {
		echo 'Connection failed: ' . $e->getMessage();
	}

}
?>
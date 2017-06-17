<?php
session_start();
include('lib/config.php');
$session_uid = '';
$_SESSION['uid'] = '';
$_SESSION['fail_sub_count'] = 0;

if(empty($session_uid) && empty($_SESSION['uid'])) {
	$url = BASE_URL . 'login.php';
	header("Location: $url");
	exit();
}
?>
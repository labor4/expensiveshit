<?php 
session_start();

$uid = posix_getuid();
$myhome = exec('echo $HOME');
if (strpos($myhome, "/./") === false){
	$myhome = $myhome;
} else {
	$myhome = explode("/./", $myhome);
	$myhome = "/".$myhome[1];
}

define("MYHOME",$myhome.'/domains/'.$_SERVER['SERVER_NAME']);
if (!isset($_SERVER['DOCUMENT_ROOT']) || $_SERVER['DOCUMENT_ROOT'] ==''){
	$_SERVER['DOCUMENT_ROOT'] = MYHOME . '/httpdocs';
}

ini_set("log_errors", 1);
ini_set("error_log", MYHOME."/logs/debug.log");

setlocale (LC_ALL, 'de_CH.utf8');
date_default_timezone_set('Europe/Zurich');
					

#include_once($_SERVER['DOCUMENT_ROOT'].'/con/kazet.php');


include_once($_SERVER['DOCUMENT_ROOT'].'/inc/class.seaerpost.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/inc/PluploadHandler.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/conf/seafile.conf.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/conf/get.conf.php');




/*
$kzdb = 		new Database(DB_SERVER, DB_USER, DB_PASS, DB);
$kzdb->connect();
*/

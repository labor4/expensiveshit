<?php
include_once('../funktionen.php');


$ph = new PluploadHandler(array(
	'target_dir' => $_SERVER['DOCUMENT_ROOT'].'/uploads/',
	//'allow_extensions' => 'jpg,jpeg,png,pdf'
));

$ph->sendNoCacheHeaders();
$ph->sendCORSHeaders();

if ($result = $ph->handleUpload()) {
	//file_put_contents($_SERVER['DOCUMENT_ROOT']."/logg.log", print_r($result,true), FILE_APPEND);
	if(!isset($result['chunk'])){
		// Finished. We can upload.
		$uppar = new SEAer();
		$uppar->loadvars(array(	"server" 		=> $seaf_conf['server'],
								"login" 		=> $seaf_conf['login'],
								"pass" 			=> $seaf_conf['pass'],
								"repo" 			=> $seaf_conf['repo'],
								"dir_base" 		=> "/KAZET_UPLOADS",
								"dir_baseup" 	=> date("Y-m-d"),
								"file" 			=> $result['finalpath'],
								"filename" 		=> ""
							));
		$uppar->execute();
		unlink($result['finalpath']);
		
	}
	die(json_encode(array(
		'OK' => 1,
		'info' => $result
	)));
} else {
	die(json_encode(array(
		'OK' => 0,
		'error' => array(
			'code' => $ph->getErrorCode(),
			'message' => $ph->getErrorMessage()
		)
	)));
}
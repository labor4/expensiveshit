<?php

/*
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
*/

// $uppar = new SEAer();
// $uppar->loadvars(array(	"server" 		=> "https://seafile.yourdomain.ch",
// 							"login" 		=> "yourlogin@yourdomain.ch",
// 							"pass" 			=> "INSERTPASSWORDT,
// 							"repo" 			=> "SEAFILE_REPO",
// 							"dir_base" 		=> "/KAZET_UPLOADS",
// 							"dir_baseup" 	=> "/path/to/target/seafile/dir/inside/dir_base",
// 							"file" 			=> "./localfiletoupload.txt",
// 							"filename" 		=> ""
// 					));
// $uppar->execute();


class SEAer {
	
	private $server 		= "";
	private $login 			= "";
	private $pass 			= "";
	private $file			= "";
	private $chooserepo 	= "";
	
	public $file_name		= "";
	
	private $dir_base		= "";
	private $dir_baseup		= "";
	
	private $token 				= "";
	private $repoid				= "";
	private $dir_lastgood 		= "";
	private $dir_lastgoodup 	= "";
	private $uploadticket		= "";
	
	private $debug;
	

	public function loadvars($entries){
		
		$this->server 		= $entries['server'];
		$this->login 		= $entries['login'];
		$this->pass 		= $entries['pass'];
		$this->chooserepo 	= $entries['repo'];
		$this->dir_base 	= "/".(trim($entries['dir_base'],"/"));
		$this->dir_baseup 	= "/".trim($entries['dir_baseup'],"/");
		$this->file 		= realpath($entries['file']);
		$this->file_name	= $entries['filename']==""?(basename($entries['file'])):$entries['filename'];
		
	}
	
	
	public function execute(){
		
		$this->gettoken();
		$this->getrepoid();
		$this->findbestpath();

		$this->makedirup();
		$this->getupload_ticket();
		$this->uploadfile();
		
		$this->token 			= '';
		$this->repoid 			= '';
		$this->dir_lastgood 	= '';
		$this->dir_lastgoodup 	= '';
		$this->uploadticket 	= '';
		$this->file_name 		= '';
		
	}
	

	private function gettoken(){
		
		$url = $this->server."/api2/auth-token/";
		$fields['username'] = $this->login;
		$fields['password'] = $this->pass;
		$header 			= array();
		$header[] 			= "Accept: application/json; indent=4";
		$answer 			= $this->mycurlload($url, $fields, "post", $header);
	
		$answer 			= json_decode($answer);
		if(!isset($answer->token)) die("no token");
		
		$this->token 		= $answer->token;
		
	}
	

	private function getrepoid(){
		
		$this->repoid = '';
		$url = $this->server."/api2/repos/";
		$header = array();
		$fields = array();
		$header[] = "Authorization: Token ".$this->token;
		$header[] = "Accept: application/json; indent=4";
		
		$answer = $this->mycurlload($url, $fields, "get", $header);
		$answer = json_decode($answer);
		
		foreach ($answer as $thisrepo){
			if($thisrepo->name == $this->chooserepo){
				$this->repoid = $thisrepo->id;
				break;
			}
		}
		if($this->repoid == ''){
			die("Repo does not exist.");
		}
		
	}
	
	
	private function findbestpath(){
		
		if(empty($this->repoid)) die("no repoid");
		
		$url = $this->server."/api2/repos/".$this->repoid."/dir/";
		$fields = array();
		$fields['p']=$this->dir_base."";
		$fields['t']='d';
		$fields['recursive'] = 1;
		$header = array();
		$header[] = "Authorization: Token ".$this->token;
		$header[] = "Accept: application/json; charset=utf-8; indent=4";
		$answer = $this->mycurlload($url, $fields, "get", $header);
		$answer = json_decode($answer);

		if(isset($answer->error_msg)){
			if(preg_match("/Folder.*not\sfound/", $answer->error_msg)){
				$url = $this->server."/api2/repos/".$this->repoid."/dir/?p=".$this->dir_base;
				$fields = array();
				$fields['operation'] = "mkdir";
				$header = array();
				$header[] = "Authorization: Token ".$this->token;
				$header[] = "Accept: application/json; charset=utf-8; indent=4";
				$answer = $this->mycurlload($url, $fields, "post", $header);
				$answer = array(); // we do this to simulate emtpy folder $this->dir_base
			} else {
				die("hard");
			}
		}
		
		$targetdir_work=$this->dir_baseup;
		
		$bastard = '';
		$i=0;
		$done = false;
		while(!$done){
			
			
			if(empty($answer)){
				$finalid="base";
				break;
			}
			foreach($answer AS $id => $thisdir){
				if($thisdir->parent_dir == $this->dir_base.$targetdir_work && ($thisdir->name == $bastard || $bastard == '')){
					if($bastard == ''){
						$finalid="base";
					} else {
						$finalid=$id;
					}
					$done = true;
					break;
				}
			}

			$targetdir_work = explode("/", $targetdir_work);
			$bastard = array_pop($targetdir_work);
			$targetdir_work = implode("/", $targetdir_work);
			if($targetdir_work == '') $targetdir_work = '/';
			$i++;
			
		}

		if($finalid === "base"){
			$lastdirgood=$this->dir_base;
		} else {
			$thechosenone = $answer[$finalid];
			$lastdirgood=$thechosenone->parent_dir.'/'.$thechosenone->name;
			$lastdirgood=str_replace("//", "/", $lastdirgood);
		}
		$targetdir_work=$this->dir_base.$this->dir_baseup;
	
		if (substr($targetdir_work, 0, strlen($lastdirgood)) == $lastdirgood) {
		    $targetdir_work = substr($targetdir_work, strlen($lastdirgood));
		} 
		$this->dir_lastgood 		= $lastdirgood;
		$this->dir_lastgoodup 		= $targetdir_work;
		
	}	
	
	
	private function myprint($arr){
		echo "<pre>";
		print_r($arr);
		echo "</pre>";
	}
	
	
	private function makedirup(){
		
		$dirstomake_arr = explode("/", $this->dir_lastgoodup);
		$dirstomake_arr = array_filter($dirstomake_arr);
	
		$nextddir = $this->dir_lastgood;
		foreach ($dirstomake_arr AS $dirtomakeanddo){
			$nextddir = $nextddir."/".$dirtomakeanddo;
			$url = $this->server."/api2/repos/".$this->repoid."/dir/?p=$nextddir";
			$fields = array();
			$fields['operation'] = "mkdir";
			$header = array();
			$header[] = "Authorization: Token ".$this->token;
			$header[] = "Accept: application/json; charset=utf-8; indent=4";
			$answer = $this->mycurlload($url, $fields, "post", $header);
		}
		
	}
	
	
	private function getupload_ticket(){
		
		$targetdir_work=$this->dir_base.$this->dir_baseup;
	
		$url = $this->server."/api2/repos/".$this->repoid."/upload-link/";
		$fields = array();
		$fields['p'] = $targetdir_work;
		$header = array();
		$header[] = "Authorization: Token ".$this->token;
		$header[] = "Accept: application/json; charset=utf-8; indent=4";
		$answer = $this->mycurlload($url, $fields, "get", $header);
	
		$this->uploadticket = trim($answer,'"');
		
	}
	
	
	private function uploadfile(){
		
		$url = $this->uploadticket;
		$fields = array();


		$memme = mime_content_type($this->file);
		$fields['file'] = new CurlFile($this->file,$memme,$this->file_name);
		$fields['parent_dir'] = $this->dir_base.$this->dir_baseup; //$targetdir_work; FIXME
		$header = array();
		$header[] = "Authorization: Token ".$this->token;
		$answer = $this->mycurlload($url, $fields, "upload", $header);
		print_r($answer);
		
	}


	private function mycurlload($url, $fields=array(), $method = 'post', $header=array(), $auth=''){

		if($method == 'post'){
			$fields_string = '';

			if(is_array($fields)){
				foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
			} else {
				$fields_string = $fields;
			}
			rtrim($fields_string, '&');
			
			$ch = curl_init();
			
			curl_setopt($ch,CURLOPT_URL, $url);
			if(!empty($header)) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			if(!empty($auth)) curl_setopt($ch, CURLOPT_USERPWD, $auth); // $auth = "user:pwd";
			
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch,CURLOPT_POST, 1);
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

			$result = curl_exec($ch);

			curl_close($ch);
			return $result;
		}
		if($method == 'upload'){
			$fields_string = '';

			$ch = curl_init();
			
			curl_setopt($ch,CURLOPT_URL, $url);
			if(!empty($header)) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			if(!empty($auth)) curl_setopt($ch, CURLOPT_USERPWD, $auth); // $auth = "user:pwd";
			
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch,CURLOPT_POST, 1);
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

			$result = curl_exec($ch);

			curl_close($ch);
			return $result;
		}
		if($method == 'get'){
			$fields_string = '';

			if(is_array($fields)){
				foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
			} else {
				$fields_string = $fields;
			}
			rtrim($fields_string, '?$fields_string');

			$ch = curl_init();

			curl_setopt($ch,CURLOPT_URL, $url."?$fields_string");
			if(!empty($header)) curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
			if(!empty($auth)) curl_setopt($ch, CURLOPT_USERPWD, $auth); // $auth = "user:pwd";
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch,CURLOPT_POST, 0);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

			$result = curl_exec($ch);

			curl_close($ch);
			
			return $result;
			
		}

	}

}

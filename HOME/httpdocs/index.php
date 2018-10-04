<?php 


include_once('funktionen.php');

if (!isset($_GET['pw']) || $_GET['pw'] != $MYGETPW){

	die("You place no here");

}


?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1">

<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black" />



<link rel="apple-touch-icon" sizes="180x180" href="/bilder/core/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/bilder/core/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/bilder/core/favicon-16x16.png">
<link rel="manifest" href="/bilder/core/site.webmanifest">
<link rel="mask-icon" href="/bilder/core/safari-pinned-tab.svg" color="#5bbad5">
<link rel="shortcut icon" href="/bilder/core/favicon.ico">
<meta name="msapplication-TileColor" content="#da532c">
<meta name="msapplication-config" content="/bilder/core/browserconfig.xml">
<meta name="theme-color" content="#ffffff">

<title>Expensive Shit</title>

<link rel="stylesheet" href="/js/plupload-3.1.2/js/jquery.plupload.queue/css/jquery.plupload.queue.css" type="text/css" media="screen" />
<link rel="stylesheet" href="/fonts/faces.css" type="text/css" />


<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>

<!-- production -->
<script type="text/javascript" src="/js/plupload-3.1.2/js/plupload.full.min.js"></script>
<script type="text/javascript" src="/js/plupload-3.1.2/js/jquery.plupload.queue/jquery.plupload.queue.js"></script>
<script type="text/javascript" src="/js/plupload-3.1.2/js/i18n/en.js"></script>

<!-- debug
<script type="text/javascript" src="../../js/moxie.js"></script>
<script type="text/javascript" src="../../js/plupload.dev.js"></script>
<script type="text/javascript" src="../../js/jquery.plupload.queue/jquery.plupload.queue.js"></script>
-->

<style>
	body{
		margin:0;
		padding: 0;
		background-color: black;
		font-family: "helvedude_light";
		font-weight: normal;
		font-style: normal;
		font-size: 13px;
	}
	h1.title{
		margin: 0px;
		padding: 0px;
		font-family: "helvedude_light";
		font-weight: normal;
		font-style: normal;
		margin-left: 8px;
		margin-top: 8px;
		color: #f55a22;
		
	}
	p.footer,
	p.footer a{
		font-family: "helvedude_light";
		font-weight: normal;
		font-style: normal;
		margin-left: 10px;
		margin-top: 0px;
		font-size: 13px;
		color: rgba(237, 193, 177, 0.61);
	}
	p.footer{
		margin: 0px;
		padding: 0px;
	}
	.plupload_header_title{
		font-family: "helvedude_light";
		font-weight: normal;
		font-style: normal;
	}
	.plupload_button{
		font-family: "helvedude_light";
		font-weight: normal;
		font-style: normal;
	}
	.plupload_container{
		background: transparent;
	}
	.plupload_file_size, .plupload_file_status{
		width: auto;
		padding-left: 10px;
	}
	div.plupload_filelist_header div.plupload_file_size,
	div.plupload_filelist_header div.plupload_file_status{
		display: none;
	}
	
</style>
</head>
<body style="">

<form method="post" action="dump.php">
<!-- 	<h1 class="title">Expensive Shit</h1> -->
	<img border="0" src="/bilder/core/expensiveshit.png" /></br>
	<div id="uploader">
		<p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p>
	</div>
	<p class="footer"><a href="<?php echo ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>"><?php echo $_SERVER['HTTP_HOST']; ?></a></p>
</form>

<script type="text/javascript">
$(function() {

	// Setup html5 version
	$("#uploader").pluploadQueue({
		// General settings
		runtimes : 'html5,flash,silverlight,html4',
		url : '/answ/plupuploader.php',
		chunk_size: '10mb',
		rename : true,
		dragdrop: true,

		filters : {
			// Maximum file size
			max_file_size : '100mb',
			// Specify what files to browse for
/*
			mime_types: [
				{title : "Image files", extensions : "jpg,gif,png"},
				{title : "PDFs", extensions : "pdf"},
				{title : "Zip files", extensions : "zip"}
			]
*/
		},

		// Resize images on clientside if we can
		resize : {width : 320, height : 240, quality : 90},

		flash_swf_url : '/js/plupload-3.1.2/js/Moxie.swf',
		silverlight_xap_url : '/js/plupload-3.1.2/js/Moxie.xap',
        init: {
			UploadComplete: function (up, files) {
			  $(".plupload_buttons").css("display", "inline");
			  $(".plupload_upload_status").css("display", "inline");
			}
        }
	});

});
</script>

</body>
</html>

<?php
	include_once("../lib/connect_db.php");
	include_once("../lib/access_rules.php");
	include_once("../config/security.php");
	include_once("../lib/login.php");
	include_once("../lib/localization.php");
	include_once("../lib/validate.php");

        if(!isset($error)) 
                $error = '';
	/*Get data from form*/
	$name = isset($_POST['name'])?$_POST['name']:'';
	$folder = isset($_POST['folder'])?$_POST['folder']:'';
	$file = isset($_FILES['file'])?$_FILES['file']:'';
	if(!(($e = folder_id_validation($folder)) || ($e = name_validation($name)) || ($e = file_validation($_FILES['file']))))
	{
		$uploaddir = "../file_store/";
		//get the base name
		$uploadfilebase= $uploaddir .pathinfo($file['name'],PATHINFO_FILENAME); 
		$uploadfile = $uploadfilebase;
		//get extension				
		$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
		$uploadfile = $uploadfilebase .".$ext";				
		$suffixno = 0;
		//if file exists add a number at end of 
		//basename in iterative manner until we
		//are clear
		while(file_exists($uploadfile))
		{
			$uploadfile = $uploadfilebase . $suffixno . ".$ext";
			$suffixno++;
		}

		//now that we settled on target filename
		//make the move
		if(move_uploaded_file($file['tmp_name'],$uploadfile))
		{
			$query = "INSERT INTO files (folder,full_path,name,uploader,upload_time) VALUES 
						('$folder','".mysql_real_escape_string($uploadfile)."','"
						.mysql_real_escape_string($name)."','$logged_userid','"
						.time()."')";
			mysql_query($query) || ($error .= mysql_error());	
		}
		else
		{
			$error .=  _("File move failed.");
		}		
	}
	else
	{
		$error .= _('Access Denied.');
	}

	if(isset($_GET['AJAX']))
	{ 
		echo '{ "error" : "'.$error.'"}';
	}
	elseif(!(isset($DONT_REDIRECT) && $DONT_REDIRECT))
	{
		if(!isset($message))
			$message = '';
		//Hide warnings
		$warning = '';
		$redirect = "files/$folder/";
		if(strlen($error))
			setcookie('notify',$error,time()+3600,$INDEX_ROOT);
		include('redirect.php');
	}
?>	

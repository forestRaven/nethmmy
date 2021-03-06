<?php
	include_once('../lib/connect_db.php');
	include_once('../lib/login.php');
	include_once("../lib/access_rules.php");
	include_once("../lib/localization.php");
	include_once("../lib/validate.php");
	include_once("../config/general.php");
	include_once("../config/security.php");

        if(!isset($error)) 
                $error = '';

	$lab = 0;
	/* Data */
	$title = isset($_POST['title'])?$_POST['title']:'';
	$description = isset($_POST['description'])?$_POST['description']:'';
	$class = isset($_POST['class'])?$_POST['class']:0;
	$team_limit = isset($_POST['team_limit'])?$_POST['team_limit']:0;
	$users_per_team_limit = isset($_POST['users_per_team_limit'])?$_POST['users_per_team_limit']:0;
	$registration_expire = isset($_POST['register_expire'])?$_POST['register_expire']:0;
	$upload_limit = isset($_POST['upload_limit'])?$_POST['upload_limit']:0;
	$upload_expire = isset($_POST['upload_expire'])?$_POST['upload_expire']:time();
	$can_free_join =  isset($_POST['can_free_join'])?$_POST['can_free_join']:0;
	$can_make_new_teams =  isset($_POST['can_make_new_teams'])?$_POST['can_make_new_teams']:0;
	$can_lock_teams =  isset($_POST['can_lock_teams'])?$_POST['can_lock_teams']:0;
	$can_upload =  isset($_POST['can_upload'])?$_POST['can_upload']:0;
	/* check if input is valid */
	if(!(($e = name_validation($title)) || ($e = class_id_validation($class)) || ($e = xml_validation($description))
	   ||($e = lab_team_limit_validation($team_limit)) || ($e = lab_team_size_limit_validation($users_per_team_limit))
	   ||($e = lab_upload_limit_validation($upload_limit)) || ($e = deadline_validation($registration_expire))
	   ||($e = deadline_validation($upload_expire)) || ($e = boolean_int_validation($can_free_join))
	   ||($e = boolean_int_validation($can_make_new_teams)) || ($e = boolean_int_validation($can_lock_teams))))
	{
		if(can_create_lab($logged_userid,$class))
		{
				//make folder if needed
				$folder = 0;
				if($can_upload)
				{
					$query = "INSERT INTO file_folders (name,class,public)
						VALUES('".mysql_real_escape_string($title)."','$class','0')";
					mysql_query($query) || ($error .= mysql_error());
					$folder = mysql_insert_id();
				}
				$time = time();
				$query = "INSERT INTO labs 
						(title,description,class,team_limit,users_per_team_limit,register_expire,
						 upload_limit,upload_expire,can_free_join,can_make_new_teams,can_lock_teams,folder,
						 creation_time,update_time)
						VALUES
						('".mysql_real_escape_string($title)."','".mysql_real_escape_string(sanitize_html($description))."','$class','$team_limit','$users_per_team_limit','$registration_expire','$upload_limit','$upload_expire','$can_free_join',
						 '$can_make_new_teams','$can_lock_teams','$folder','$time','$time')";
				mysql_query($query) || ($error .= mysql_error());
				$lab = mysql_insert_id();
		}
		else
		{
			$error .= _('Access denied.');
		}
	}
	else
	{
		$error .= $e;
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
		$redirect = ($error)?"new_lab/$class/":"lab/$lab/";
		if(strlen($error))
			setcookie('notify',$error,time()+3600,$INDEX_ROOT);
		include('redirect.php');
	}
	
?>

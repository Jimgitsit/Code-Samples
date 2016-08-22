<?php
	$email_to = "kalden@builtbyhq.com, zarin@builtbyhq.com";
	$email_subject = "Contact Form";
	 
	$name = $_POST['name']; // required
	$email_from = $_POST['email']; // required
	$message = $_POST['message']; // required
	$phone = $_POST['phone']; // required
	$have = $_POST['have']; // required
	 
	/*
	The following function checks for email injection.
	Specifically, it checks for carriage returns - typically used by spammers to inject a CC list.
	*/
	function isInjected($str) {
       $injections = array('(\n+)',
       '(\r+)',
       '(\t+)',
       '(%0A+)',
       '(%0D+)',
       '(%08+)',
       '(%09+)'
       );
       $inject = join('|', $injections);
       $inject = "/$inject/i";
       if(preg_match($inject,$str)) {
               return true;
       }
       else {
               return false;
       }
	}


	$email_message = "Form details below.\n\n";
	$email_message .= "Name: ".$name."\n";
	$email_message .= "Email: ".$email_from."\n";
	$email_message .= "Message: ".$message."\nPhone: ".$phone."\n".$have."";

	$header = "Reply-To: ".$email_from."\r\n"; 
	$header .= "Return-Path: ".$email_from."\r\n"; 
	$header .= "From: \r\n";
	$header .= "Organization: \r\n"; 
	$header .= "Content-Type: text/html\r\n";

	$error_page = '../error.php';
	$success = '../';

	if ( isInjected($email_from) ) {
	header( "Location: $error_page" );
	}
	else {
	@mail($email_to, $email_subject, $email_message, $headers);
	header( "Location: $success" );
	}
?>

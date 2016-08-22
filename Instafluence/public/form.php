<?php

	if(!empty($_POST)) {
		$to = "contact.instafluence@gmail.com";

		$subject = "Influencer Contact";

		$email  = "First Name: {$_POST[ 'first_name' ]}\n";
		$email .= "Last Name: {$_POST[ 'last_name' ]}\n";
		$email .= "Email: {$_POST[ 'email' ]}\n";
		$email .= "Phone: {$_POST[ 'phone' ]}\n";
		$email .= "Social Network: {$_POST[ '00NG00000067KEU' ]}\n";
		$email .= "Username: {$_POST[ '00NG00000067KEZ' ]}\n";
		$email .= "Style of Account: {$_POST[ '00NG00000067IgZ' ]}\n";
		$email .= "Number of Followers: {$_POST[ '00NG00000067Ige' ]}\n";

		$headers = 'From: "Instafluence" <contact@instafluence.com>' . PHP_EOL .
			'Reply-To: "Instafluence" <contact@instafluence.com>' . PHP_EOL .
			'X-Mailer: PHP/' . phpversion();

		if(mail($to, $subject, $email, $headers)) {
			echo('success');
		}
		else {
			echo('failed');
		}
	}

?>
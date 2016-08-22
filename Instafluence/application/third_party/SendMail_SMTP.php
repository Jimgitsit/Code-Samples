<?php

require_once( APPPATH . 'config/sendmail_config.php' );
require_once( APPPATH . 'third_party/phpmailer/class.phpmailer.php' );

class SendMail_SMTP {
	/**
	 * Sends an email using PHPMailer via SMTP.
	 *
	 * Requires the following defines:
	 * 		SMTP_HOST
	 * 		SMTP_PORT
	 * 		SMTP_USERNAME
	 * 		SMTP_PASSWORD
	 * 		SMTP_SENDER_ADDR
	 * 		SMTP_SENDER_NAME
	 * 		SMTP_USE_SSL
	 * 		MAILER_LOG
	 *
	 * (Does not currently support images but that can be easily added).
	 *
	 * @param string $to
	 * @param string $cc
	 * @param string $subject
	 * @param string $htmlBody
	 * @param string $txtBody
	 * @param string $from
	 * @param string $fromName
	 * @return boolean
	 * @author JM
	 */
	public static function sendEmail( $to, $cc, $subject, $htmlBody, $txtBody, $from = null, $fromName = null, $replyTo = null, $replyToName = null ) {
		if( !defined( 'SMTP_HOST' ) || SMTP_HOST == '' ||
			!defined( 'SMTP_PORT' ) || SMTP_PORT == '' ||
			!defined( 'SMTP_USERNAME' ) || SMTP_USERNAME == '' ||
			!defined( 'SMTP_PASSWORD' ) || SMTP_PASSWORD == '' ) {
				$success = file_put_contents( MAILER_LOG, 'Error: Missing required defines in sendmail_config.php.', FILE_APPEND );
				return false;
		}

		$mail             = new PHPMailer();

		$mail->IsSMTP();
		$mail->SMTPAuth   = true;
		if( SMTP_USE_SSL ) {
			$mail->SMTPSecure = "ssl";
		}
		$mail->Host       = SMTP_HOST;
		$mail->Port       = SMTP_PORT;

		$mail->Username   = SMTP_USERNAME;
		$mail->Password   = SMTP_PASSWORD;

		if( $from != null ) {
			$mail->From = $from;
		}
		else {
			$mail->From	= SMTP_SENDER_ADDR;
		}

		if( $fromName != null ) {
			$mail->FromName = $fromName;
		}
		else {
			$mail->FromName = SMTP_SENDER_NAME;
		}
		
		if ($replyTo === null) {
			$replyTo = SMTP_SENDER_ADDR;
		}
		
		if ($replyToName === null) {
			$replyToName = SMTP_SENDER_NAME;
		}

		$mail->AddReplyTo( $replyTo, $replyToName );
		
		$mail->Subject    = $subject;
		$mail->AltBody    = $txtBody;
		//$mail->WordWrap   = 50;

		$mail->MsgHTML( $htmlBody );
		
		if(is_array($to)) {
			foreach($to as $email) {
				$mail->AddAddress( $email );
			}
		}
		else {
			$mail->AddAddress( $to );
		}
		if( !empty( $cc ) ) {
			$mail->AddCC( $cc );
		}
		$mail->IsHTML( true );

		//$mail->AddEmbeddedImage( 'img/invoice_logo.jpg', 'invoice_logo' );
		// and on the <img> tag put src='cid:invoice_logo'

		$date = new DateTime();
		if( !$mail->Send() ) {
			$success = file_put_contents( MAILER_LOG, $date->format('Y-m-d H:i:s') . " Failed sending mail to $to: " . $mail->ErrorInfo . "\n", FILE_APPEND );
			return false;
		}
		else {
			$success = file_put_contents( MAILER_LOG, $date->format('Y-m-d H:i:s') . " Successfuly sent email to $to. Subject: $subject\n", FILE_APPEND );
		}

		return true;
	}
}
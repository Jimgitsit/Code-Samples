<?php

require_once( 'sendmail_config.php' );
require_once( 'class.phpmailer.php' );
require_once('GCMPushMessage.php');
require_once('ApnsPHP/Autoload.php');
require_once('mailgun-php/vendor/autoload.php');
use Mailgun\Mailgun;

/**
 * Class Util
 * 
 * Misc static utility functions.
 * 
 * @author Jim McGowen
 */
class Util {
	/**
	 * Sends an email using PHPMailer via SMTP.
	 * 
	 * Requires the following defines:
	 * 		SMTP_HOST
	 * 		SMTP_PORT
	 * 		SMTP_USERNAME
	 * 		SMTP_PASSWORD
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
	 */
	 public static function sendEmail( $to, $cc, $subject, $htmlBody, $txtBody, $from = null, $fromName = null ) {
		/*
		echo 'to-'.$to.'<br>';
		echo 'subject-'.$subject.'<br>';
		echo 'name-'.$fromName.'<br>';
		echo 'body-'.$txtBody.'<br>';
		echo 'body-'.$txtBody.'<br>';
		exit(); */
		
		
		/*
         New code using MailGun.com for Email server
         **Need to add mailgun-php sdk**
         https://github.com/mailgun/mailgun-php
         Documentation: https://documentation.mailgun.com/user_manual.html#sending-via-api
         */
       
        //Using REST API
        # Instantiate the client.
        //$mgClient = new Mailgun('key-aaf353cd26fb2b4c3a871f907a7f108a');
        //$domain = "mg.vrmobileapp.com";
        $mgClient = new Mailgun('key-aaf353cd26fb2b4c3a871f907a7f108a');
        $domain = "https://api.mailgun.net/v3/mg.vrmobileapp.com";
        $data = array();
        
        $fromName = str_replace(" ", "", $fromName);
        $fromName = str_replace(".", "", $fromName);
        $fromName = str_replace(",", "", $fromName);
        
        if($cc){
	        $cc = str_replace(" ", ",", $cc);
	        $data = array(
		        'from'    => "noreply@virtualrain.com",
		        'to'      => $to,
		        'cc'      => $cc,
		        'subject' => $subject,
		        'text'    => $txtBody,
		        'html'    => $htmlBody
		    );
        }	
        else {
	        $data = array(
		        'from'    => "noreply@virtualrain.com",
		        'to'      => $to,
		        'subject' => $subject,
		        'text'    => $txtBody,
		        'html'    => $htmlBody
		    );
        }
        	
		# Make the call to the client.
        $result = $mgClient->sendMessage($domain, $data);
      
	    return true;
       
	}

	
	/**
	 * Formats a location from an arry into HTML.
	 * 
	 * $locArr should have:
	 * 		$locArr[ 'name' ]
	 * 		$locArr[ 'address1' ]
	 * 		$locArr[ 'address2' ]
	 * 		$locArr[ 'city' ]
	 * 		$locArr[ 'state' ]
	 * 		$locArr[ 'zip' ]
	 * 
	 * @param unknown $locArr
	 * @param string $html
	 * @return string
	 */
	public static function formatLocation( $locArr, $html = true ) {
		$output = $locArr[ 'name' ];
		$html ? $output .= '<br/>' : $output .= "\n";
		$output .= $locArr[ 'address1' ];
		$html ? $output .= '<br/>' : $output .= "\n";
		if( !empty( $locArr[ 'address2' ] ) ) {
			$output .= $locArr[ 'address2' ];
			$html ? $output .= '<br/>' : $output .= "\n";
		}
		$output .= $locArr[ 'city' ] . ', ' . $locArr[ 'state' ] . ' ' . $locArr[ 'zip' ];
		
		return $output;
	}

	/**
	 * Sends a new order email.
	 * 
	 * @param $dist
	 * @param $to
	 * @param $order
	 * @param $user
	 */
	public static function emailNewOrder($dist, $to, $order, $user, $cc) {
		$orderNum = str_pad($order['id'], 6, '0', STR_PAD_LEFT);
		$subject = 'New Order - #' . $orderNum;
			
		$html = file_get_contents( 'templates/admin/res/new_order_email.html' );
		$text  = file_get_contents( 'templates/admin/res/new_order_email.txt' );
		
		// Shipping or pickup locaiton
		$locationType = '';
		$locationHtml = '';
		if($order['pickup_location'] && $order['pickup_location'] != 0) {
			$locationType = 'Pickup location';
		}
		else {
			$locationType = 'Shipping location';
		}
		$locationHtml = Util::formatLocation($order['location_info']);
		$locationText = Util::formatLocation($order['location_info'], false);
		
		// Products
		$productsHtml = '';
		foreach($order['cart'] as $item) {
			$productsHtml .= $item['quantity'] . ' - ' . $item['product']['title'];
			if($item['style']['style_num'] != $item['product']['part_num']) {
				$productsHtml .= ', Style: ' . $item['style']['style_num'] . ' - ' . $item['style']['style_description'];
			}

			if ($user['show_pricing']) {
				$productsHtml .= ', $' . number_format($item['style']['price'], 2, '.', ',');
			}
			
			if ($dist['show_units'] && !empty($item['style']['unit'])) {
				if ($user['show_pricing'] != '1') {
					$productsHtml .= ', ' . $item['style']['unit'];
				}
				else {
					$productsHtml .= ' /' . $item['style']['unit'];
				}
			}
				
			$productsHtml .= ', SKU: ' . $item['product']['sku'] . ', PN: ' . $item['product']['part_num'] . '<br/>';
		}
		$productsText = str_replace('<br/>', "\n", $productsHtml);
		
		// Templating
		$html = str_replace( '{orderNum}', $orderNum, $html );
		$html = str_replace( '{orderTotal}', '$' . number_format($order['total'], 2, '.', ','), $html );
		$html = str_replace( '{companyName}', $user['company_name'], $html );
		$html = str_replace( '{userName}', $user['first_name'] . ' ' . $user['last_name'], $html );
		$html = str_replace( '{userEmail}', $user['email'], $html );
		$html = str_replace( '{phoneNumber}', $user['cell_phone'], $html );
		$html = str_replace( '{accountNumber}', $user['account_num'], $html );
		$html = str_replace( '{poNum}', $order['po_num'], $html );
		$html = str_replace( '{comments}', $order['order_comment'], $html );
		$html = str_replace( '{locationType}', $locationType, $html );
		$html = str_replace( '{location}', $locationHtml, $html );
		$html = str_replace( '{products}', $productsHtml, $html );
		
		$text = str_replace( '{orderNum}', $orderNum, $text );
		$text = str_replace( '{orderTotal}', '$' . number_format($order['total'], 2, '.', ','), $text );
		$text = str_replace( '{companyName}', $user['company_name'], $text );
		$text = str_replace( '{userName}', $user['first_name'] . ' ' . $user['last_name'], $text );
		$text = str_replace( '{userEmail}', $user['email'], $text );
		$text = str_replace( '{phoneNumber}', $user['cell_phone'], $text );
		$text = str_replace( '{accountNumber}', $user['account_num'], $text );
		$text = str_replace( '{poNum}', $order['po_num'], $text );
		$text = str_replace( '{comments}', $order['order_comment'], $text );
		$text = str_replace( '{locationType}', $locationType, $text );
		$text = str_replace( '{location}', $locationText, $text );
		$text = str_replace( '{products}', $productsText, $text );
		
		//CC
		$ccEmails = " " . $cc . " ";
		
		
		// Send it
		Util::sendEmail( $to, $ccEmails, $subject, $html, $text, null, $dist['company_name'] );
	}

	/**
	 * Encrypts a password. If salt is not set one will be generated randomly.
	 * Returns and array with encrypted_password and salt.
	 * 
	 * @param $plainText
	 * @param null $salt
	 *
	 * @return array
	 */
	public static function encryptPassword($plainText, $salt = null) {
		$password = array();
		if ($salt == null) {
			$password['salt'] = self::gensalt( 5 );
		}
		else {
			$password['salt'] = $salt;
		}
		
		$password['encrypted_password'] = md5( md5( $plainText ) . md5( $password['salt'] ) );
		return $password;
	}

	/**
	 * Generates a random value to use as a password salt.
	 * @param $length
	 *
	 * @return string
	 */
	private static function gensalt( $length ) {
		$characterList = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*?";
		$i = 0;
		$salt = "";
		while( $i < $length ) {
			$salt .= $characterList{ mt_rand( 0, ( strlen( $characterList ) - 1 ) ) };
			$i ++;
		}
		return $salt;
	}

	/**
	 * Send app nitifications to both Android and iPhones. 
	 * $userId can be an int for one user or and array for many.
	 * 
	 * @param $userIds array
	 * @param $msg string
	 * @param $action string Typically the url you want the web view to load.
	 */
	public static function sendNotification($userIds, $msg, $action = null) 
	{
		if (!is_array($userIds)) {
			$userIds = array($userIds);
		}

		$db = new VirtualRainDB();
		$gcmIds = $db->getGCMIds($userIds);
		Util::sendAndroidNotification($gcmIds, $msg, $action);
		
		$apnsIds = $db->getAPNSIds($userIds);
		Util::sendIOSNotification($apnsIds, $msg, $action);
	}

	/**
	 * Sends a notification via GCM to android devices.
	 * Requires GCMPushMessage.php
	 * 
	 * @param array $ids GCM registration ids.
	 * @param string $msg The message
	 * @param string $url The URL to load in the web view for the app. Do not include the domain. Example: "/order?id=101"
	 */
	private static function sendAndroidNotification($ids, $msg, $url) 
	{
		if (count($ids) == 0) {
			return;
		}
		
		$extraData = array();
		
		if ($url !== null) {
			$extraData['url'] = $url;
		}
		
		$an = new GCMPushMessage(GOOGLE_PUBLIC_API_KEY);
		$an->setDevices($ids);
		$response = $an->send($msg, $extraData);
		$resp = json_decode($response, true);
		if ($resp == null || $resp['success'] != '1') {
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/logs/notification_log.log', "\n" . strftime("%Y-%m-%d %H:%M:%S") . " Error sending Android notification: " . var_export($response, true), FILE_APPEND);
		}
	}

	/**
	 * Send a notification to iOS devices.
	 * 
	 * @param array $ids GCM registration ids.
	 * @param string $msg The message
	 * @param string $url The URL to load in the web view for the app. Do not include the domain. Example: "/order?id=101"
	 *
	 * @return bool
	 * @throws ApnsPHP_Exception
	 * @throws ApnsPHP_Message_Exception
	 * @throws ApnsPHP_Push_Exception
	 * @throws Exception
	 */
	private static function sendIOSNotification($apnsIds, $msg, $url) {
		try {
			if (count($apnsIds) == 0) {
				return;
			}
			/*
			$pemFile = $_SERVER['DOCUMENT_ROOT'] . '/res/dist_' . $_SESSION['distributer']['id'] . '.pem';
			if (!file_exists($pemFile)) {
				file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/logs/notification_log.log', "\n" . strftime("%Y-%m-%d %H:%M:%S") . " Error sending iOS notification: File not found: $pemFile", FILE_APPEND);

				return;
			}
			
			$push = new ApnsPHP_Push(ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION, $pemFile);

			$push->setProviderCertificatePassphrase('virtualrain');
			//$push->setRootCertificationAuthority($_SERVER['DOCUMENT_ROOT'] . '/res/ck.pem');
			$push->connect();
			
						
			foreach ($apnsIds as $userId => $apnsId) {
								
				$message = new ApnsPHP_Message();
				$message->addRecipient($apnsId);
				
				$count = $db->getNotificationsCount($userId);
				
				// Set badge icon
				$message->setBadge($count);
	
				// Set a simple welcome text
				$message->setText($msg);
	
				// Play the default sound
				$message->setSound();
	
				// Set another custom property
				$message->setCustomProperty('url', $url);
	
				// Add the message to the message queue
				$push->add($message);
			}

			// Send all messages in the message queue
			$push->send();

			// Disconnect from the Apple Push Notification Service
			$push->disconnect();

			// Examine the error message container
			$aErrorQueue = $push->getErrors();
			if (!empty($aErrorQueue)) {
				file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/logs/notification_log.log', "\n" . strftime("%Y-%m-%d %H:%M:%S") . " Error sending iOS notification: " . var_export($aErrorQueue, true), FILE_APPEND);
			} */
			
			

			$passphrase = 'ujyfljnhjgby123';
			
			
			$ctx = stream_context_create();
			stream_context_set_option($ctx, 'ssl', 'local_cert', $_SERVER['DOCUMENT_ROOT'] . '/res/ck.pem');
			stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
			
			// Open a connection to the APNS server
			$fp = stream_socket_client(
			'ssl://gateway.push.apple.com:2195', $err,
			$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
			
			foreach ($apnsIds as $userId => $apnsId) {
				$deviceToken = $apnsId;
				
				if (!$fp) {
					exit("Failed to connect: $err $errstr" . PHP_EOL);
				} else {
					echo 'Connected to APNS' . PHP_EOL;
				}
				
				$count = $db->getNotificationsCount($userId);
				
				// Create the payload body
				$body['aps'] = array(
					'badge' => '+'.$count,
					'alert' => $msg,
					'sound' => 'default'
				);
				
				// Encode the payload as JSON
				$payload = json_encode($body);
				
				// Build the binary notification
				$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
				
				// Send it to the server
				$result = fwrite($fp, $msg, strlen($msg));
				
				if (!$result) {
					echo 'Message not delivered' . PHP_EOL;
				} else {
					echo 'Message successfully delivered' . PHP_EOL;
				}
			}

			fclose($fp);
		}
		catch (Exception $e) {
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/logs/notification_log.log', "\n" . strftime("%Y-%m-%d %H:%M:%S") . " Error sending iOS notification: " . $e->getMessage(), FILE_APPEND);
		}
	}
}








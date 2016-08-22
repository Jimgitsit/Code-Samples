<?php

class GCMNotifier {
	private static $result;
	private static $lastError;
	
	public static function sendNotification( $regIds, $messages ) {
		if( !is_array( $regIds ) || !is_array( $messages ) ) {
			return false;
		}
		
		// Set POST variables
		$url = 'https://android.googleapis.com/gcm/send';
		
		$fields = array(
				'registration_ids'  => $regIds,
				'data'              => $messages
		);
		
		$headers = array(
				'Authorization: key=' . GCM_API_KEY,
				'Content-Type: application/json'
		);
		
		$jsonFields = json_encode( $fields );
		
		// Open connection
		$ch = curl_init();
		
		// Set the url, number of POST vars, POST data
		curl_setopt( $ch, CURLOPT_URL, $url );
		
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $jsonFields );
		
		// Execute post
		$success = true;
		$result = curl_exec( $ch );
		
		if( $result == false ) {
			GCMNotifier::$lastError = curl_error( $ch );
			$success = false;
		}
		else {
			GCMNotifier::$result = $result;
		}
		
		// Close connection
		curl_close( $ch );
		
		return $success;
	}
	
	public static function getResult() {
		return GCMNotifier::$result;
	}
	
	public static function getLastError() {
		return GCMNotifier::$lastError;
	}
}
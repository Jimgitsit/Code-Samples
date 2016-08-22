<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('BaseDefaultController.php');
require_once(APPPATH . 'third_party/SendMail_SMTP.php');

/**
 * Class Emailer
 * 
 * Built for a one-time email campaign. Sends a special holiday invite email to 
 * all users with no user connected social accounts.
 * 
 * Should be use the Mailgun config for this in /application/config/sendmail_config.php
 */
class Emailer extends BaseDefaultController
{
	const LOG_FILE_NAME = "mass_email.txt";
	const REPLY_TO_ADDRESS = "austin@instafluence.com";
	const REPLY_TO_NAME = "Austin Emery";
	const EMAIL_SUBJECT = "Happy holidays from Instafluence!";
	const EMAIL_BASE_URL_SSL = "https://instafluence.com";
	const EMAIL_BASE_URL = "http://instafluence.com";
	
	public function index()
	{
		error_reporting(E_ALL);
		
		// Do not allow running this from a browser
		if (isset($_SERVER['REMOTE_ADDR'])) {
			die('This service cannot be run from a web browser!');
		}

		// Resume after this email is encountered
		$startAfterEmail = '';
		$started = false;

		if ($startAfterEmail == '') {
			$this->resetLog();
		}

		// Get all the users for this email campaign
		$emails = $this->getUserEmails();
		
		$this->log("Sending emails...\n");
		
		foreach ($emails as $email => $token) {
			if ($startAfterEmail != '') {
				if ($started == false) {
					if ($email == $startAfterEmail) {
						$started = true;
					}
					
					continue;
				}
			}

			$this->log("$email... ");

			$content = $this->getEmailContent($token);
			$success = $this->sendEmail($email, $content['html'], $content['text']);

			if ($success) {
				$this->log("Sent\n");
			}
			else {
				$this->log("Failed!\n");
			}
		}

		$this->log("...Done\n");
	}
	
	private function getEmailContent($token) 
	{
		$token = Emailer::EMAIL_BASE_URL_SSL . "/setpassword/?t=$token";
		
		$content = array();

		$content['text'] = "Hello,\n\nHappy Holidays from the Instafluence team!  We hope you are doing well and spending time with friends and family this season.\n\nHere at Instafluence, we believe in building relationships with those we work with.  We love getting to know you and what makes your channels unique.  Ultimately, the better we know you, the better we can match you with promotional opportunities from our clients.\n\nPlease take a minute and tell us about yourself!  We have started a profile for you on our website using your email address which you can complete by clicking this link $token.  If you've already created your profile, be sure to log in and connect your accounts to your profile.  Remember, the more information you can provide, the better!\n\nAll the best,\n\nAustin Emery\nDirector of Influencer Relations\n" . Emailer::EMAIL_BASE_URL;
		$content['html'] = str_replace("\n", "<br>", $content['text']);
		
		return $content;
	}

	private function getUserEmails() 
	{
		$this->log("Getting user email list... ");
		
		$emails = array();

		$this->load->model('social_model');
		$this->load->model('user_model');

		// *** For debugging ***
		
		$testEmails = array('rooster242@gmail.com');
		$col = $this->user_model->getCollection();
		foreach ($testEmails as $email) {
			$user = $col->findOne(array('email' => $email));
			if (!empty($user) && isset($user['email'])) {
				$token = $this->user_model->setUserPwResetToken($user['email']);
				$emails[$user['email']] = $token;
			}
		}
		//return $emails;
		
		// *********************
		
		$socials = $this->social_model->getAll();
		$socailCount = 0;
		foreach ($socials as $social) {
			$socailCount++;
			
			if (!isset($social['networks']) || !is_array($social['networks'])) {
				continue;
			}
			
			$skip = false;
			if (is_array($social['networks'])) {
				foreach ($social['networks'] as $network) {
					if ($network === null) {
						continue;
					}
					
					foreach ($network as $account) {
						if ($account == null) {
							continue;
						}
						
						if (isset($account) && is_array($account) && isset($account['connected']) && $account['connected'] == true) {
							$skip = true;
							break;
						}
					}
					
					if ($skip) {
						break;
					}
				}
			}
			
			if (!$skip) {
				$col = $this->user_model->getCollection();
				$user = $col->findOne(array('email' => $social['email']));
				if (!empty($user) && isset($user['email'])) {
					$token = $this->user_model->setUserPwResetToken($user['email']);
					if ($token != null) {
						$emails[$user['email']] = $token;
					}
				}
			}
		}
		
		$this->log("Done. Got " . count($emails) . " emails.\n");
		
		return $emails;
	}

	private function sendEmail($to, $html, $text) 
	{
		$mailer = new SendMail_SMTP();
		return $mailer->sendEmail($to, '', Emailer::EMAIL_SUBJECT, $html, $text, null, null, Emailer::REPLY_TO_ADDRESS, Emailer::REPLY_TO_NAME);
	}
	
	private function resetLog() 
	{
		file_put_contents(APPPATH . 'logs/' . Emailer::LOG_FILE_NAME, '');
	}
	
	private function log($msg) 
	{
		file_put_contents(APPPATH . 'logs/' . Emailer::LOG_FILE_NAME, $msg, FILE_APPEND);
	}
}
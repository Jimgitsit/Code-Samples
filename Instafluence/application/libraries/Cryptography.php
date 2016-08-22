<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * For generating cryptography assets such as random strings,
 * salts, hashes, etc.
 *
 * @author Jason Maurer <jason@builtbyhq.com>
 * @copyright (c) 2014
 */

class Cryptography
{

	private $_charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';

	/**
	 * Creates a random salt of a given length.
	 */
	private function _generateSalt($length = 15)
	{
		$string = bin2hex(openssl_random_pseudo_bytes(20, $strong));
		if (!$strong) { die('An error has occured!'); }

		$random = '';
		$min = 0;
		$max = 62;
		for ($i=0; $i<$length; $i++) {
			$rand = mt_rand($min,$max);
			(($rand - 1) < $min ? $start = $rand + 1 : $start = $rand - 1);
			$char = substr($this->_charset,$start,1);
			$random = $random . $char;
		}

		$beforeBlock = substr($string,0,strlen($string)/2);
		$afterBlock = substr($string,strlen($string)/2,strlen($string));
		$block = substr($string,strlen($string)/2,strlen($string)/4);

		$salt = strrev($random)
			. strrev($beforeBlock)
			. $block
			. strrev(substr($afterBlock,0,strlen($afterBlock)/2))
			. substr($afterBlock,strlen($afterBlock)/2)
			. $random;

		$returnMin = mt_rand(0,strlen($salt));
		$returnMax = $length;
		if (($returnMin + $returnMax) > strlen($salt)) {
			$returnMin -= $length;
		}

		return substr($salt,$returnMin,$returnMax);
	}

	/**
	 * Creates a random code of a given length consisting of
	 * letters and numbers with multiple blocks and salts.
	 */
	private function _generateCode($length)
	{
		$string = bin2hex(openssl_random_pseudo_bytes($length/2, $strong));
		if (!$strong) { die('An error has occured!'); }

		$salt1 = $this->_generateSalt();
		$salt2 = $this->_generateSalt();
		$salt3 = $this->_generateSalt();

		$block1 = substr($string,0,$length/4);
		$block2 = substr($string,$length/4,$length/4);
		$block3 = substr($string,($length/4)+($length/4),strlen($string));

		$code = $block1 . $salt1 . $block2 . $salt2 . $block3 . $salt3;

		return substr($code,0,$length);
	}

	/**
	 * Creates a SHA256 hash based on a given key, string, and
	 * an optional salt. Runs it through multiple rounds and
	 * returns the final hash, as well as the supplied salt.
	 */
	private function _generateHash($key, $string, $salt = null)
	{
		if ($salt == null) $salt = $this->_generateSalt();

		$block1 = substr($string, 0, 1);
		$block2 = substr($string, 1, strlen($string)/2);
		$block3 = substr($string, strlen($string)/2, strlen($string));

		$hash = $block1
			. $salt
			. $block2
			. $salt
			. $block3
			. $key;

		$rounds = 50000;
		for ($i=0; $i<$rounds; $i++) {
			$hash = hash('sha256',$hash);
		}

		return array('salt'=>$salt, 'hash'=>$hash);
	}

	/**
	 * Generates a random string of a given length.
	 */
	public function randomString($length)
	{
		$string = preg_replace("/[^A-Za-z0-9 ]/", "", $this->_generateCode($length));
		return $string;
	}

	/**
	 * Generates a hash from a given string, and returns an
	 * array containing the final hash, and the used salt.
	 */
	public function hash($string, $key, $salt = null)
	{
		$hash = $this->_generateHash($key, $string, $salt);
		return array('salt'=>$hash['salt'], 'hash'=>$hash['hash']);
	}

}
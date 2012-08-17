<?php
/**
* All code based heavily upon Undesigned Amazon S3 PHP Class located at: http://undesigned.org.za/2007/10/22/amazon-s3-php-class
* Copyright (c) 2008, Donovan Sch�nknecht.  All rights reserved.
*
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions are met:
*
* - Redistributions of source code must retain the above copyright notice,
*   this list of conditions and the following disclaimer.
* - Redistributions in binary form must reproduce the above copyright
*   notice, this list of conditions and the following disclaimer in the
*   documentation and/or other materials provided with the distribution.
*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
* AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
* IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
* ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
* LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
* CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
* SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
* INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
* CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
* ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
* POSSIBILITY OF SUCH DAMAGE.
*
* Amazon S3 is a trademark of Amazon.com, Inc. or its affiliates.
*/

/*
*	Modifications to Undesigned S3 class are licensed under the Creative Commons Attribution-ShareAlike 3.0 Unported License.
*	To view a copy of this license, visit http://creativecommons.org/licenses/by-sa/3.0/ or send a letter to
*	Creative Commons, 171 Second Street, Suite 300, San Francisco, California, 94105, USA.
*
*	@created-by: Zane Shannon (zcs.me or @zcs)
*	@created-on: January 25, 2011
*	@version: 0.1
*	@link: https://github.com/zshannon/Amazon-Simple-Email-Service-PHP
*/


//	Comment the following line if you don't need to use a root certificate file
define('CERTIFICATE_FILE_PATH', dirname(__FILE__).'/cacert.pem');

class SES {

	private static $__service_version = '2010-12-01';

	private static $__accessKey;
	private static $__secretKey;

	public function __construct($accessKey = null, $secretKey = null) {
		if ($accessKey !== null && $secretKey !== null) self::setAuth($accessKey, $secretKey);
		else return false;
	}

	public static function setAuth($accessKey, $secretKey) {
		self::$__accessKey = $accessKey;
		self::$__secretKey = $secretKey;
	}

	public static function listVerifiedAddresses() {
		$rest = new SESRequest('GET', 'ListVerifiedEmailAddresses');
		$rest = $rest->getResponse();
		if ($rest->error === false && $rest->code !== 200) $rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		if ($rest->error !== false) {
			trigger_error(sprintf("SES::listVerifiedAddresses(): [%s] %s", $rest->error['code'], $rest->error['message']), E_USER_WARNING);
			return false;
		}
		$rest = objectsIntoArray($rest);
		$response = array('request_id'=>$rest['body']['ResponseMetadata']['RequestId'],
						'addresses'=>$rest['body']['ListVerifiedEmailAddressesResult']['VerifiedEmailAddresses']['member']);
		return $response;
	}

	public static function verifyAddress($address) {
		$rest = new SESRequest('GET', 'VerifyEmailAddress', array('EmailAddress'=>$address));
		$rest = $rest->getResponse();
		if ($rest->error === false && $rest->code !== 200)
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		if ($rest->error !== false) {
			trigger_error(sprintf("SES::verifyAddress(): [%s] %s", $rest->error['code'], $rest->error['message']), E_USER_WARNING);
			return false;
		}
		$rest = objectsIntoArray($rest);
		$response = array('request_id'=>$rest['body']['ResponseMetadata']['RequestId']);
		return $response;
	}

	public static function deletedVerifiedAddress($address) {
		$rest = new SESRequest('GET', 'DeleteVerifiedEmailAddress', array('EmailAddress'=>$address));
		$rest = $rest->getResponse();
		if ($rest->error === false && $rest->code !== 200)
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		if ($rest->error !== false) {
			trigger_error(sprintf("SES::deletedVerifiedAddress(): [%s] %s", $rest->error['code'], $rest->error['message']), E_USER_WARNING);
			return false;
		}
		$rest = objectsIntoArray($rest);
		$response = array('request_id'=>$rest['body']['ResponseMetadata']['RequestId']);
		return $response;
	}

	public static function getSendQuota() {
		$rest = new SESRequest('GET', 'GetSendQuota');
		$rest = $rest->getResponse();
		if ($rest->error === false && $rest->code !== 200) $rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		if ($rest->error !== false) {
			trigger_error(sprintf("SES::getSendQuota(): [%s] %s", $rest->error['code'], $rest->error['message']), E_USER_WARNING);
			return false;
		}
		$rest = objectsIntoArray($rest);
		$response = array('request_id'=>$rest['body']['ResponseMetadata']['RequestId']);
		$response = array_merge($response, $rest['body']['GetSendQuotaResult']);
		return $response;
	}

	public static function getSendStatistics() {
		$rest = new SESRequest('GET', 'GetSendStatistics');
		$rest = $rest->getResponse();
		if ($rest->error === false && $rest->code !== 200) $rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		if ($rest->error !== false) {
			trigger_error(sprintf("SES::getSendStatistics(): [%s] %s", $rest->error['code'], $rest->error['message']), E_USER_WARNING);
			return false;
		}
		$rest = objectsIntoArray($rest);
		$response = array('request_id'=>$rest['body']['ResponseMetadata']['RequestId']);
		$response = array_merge($response, $rest['body']['GetSendStatisticsResult']['SendDataPoints']);
		return $response;
	}

	public static function sendEmail($email) {
		$formatted_email = array();
		foreach (array_keys($email) as $key) {
			if (is_string($email[$key])) $formatted_email[$key] = $email[$key];
		}
		foreach (array_keys($email['Destination']) as $list) {
			for($i=0;$i<count($email['Destination'][$list]);$i++)
				$formatted_email['Destination.'.$list.'.member.'.($i+1)] = $email['Destination'][$list][$i];
		}
		$formatted_email['Message.Subject.Data'] = $email['Message']['Subject']['Data'];
		if (isset($email['Message']['Subject']['Charset']))
			$formatted_email['Message.Subject.Charset'] = $email['Message']['Subject']['Charset'];
		$formatted_email['Message.Body.Html.Data'] = $email['Message']['Body']['Html']['Data'];
		if (isset($email['Message']['Body']['Html']['Charset']))
			$formatted_email['Message.Body.Html.Charset'] = $email['Message']['Body']['Html']['Charset'];
		$formatted_email['Message.Body.Text.Data'] = $email['Message']['Body']['Text']['Data'];
		if (isset($email['Message']['Body']['Text']['Charset']))
			$formatted_email['Message.Body.Text.Charset'] = $email['Message']['Body']['Text']['Charset'];
		$rest = new SESRequest('POST', 'SendEmail', $formatted_email);
		$rest = $rest->getResponse();
		if ($rest->error === false && $rest->code !== 200) $rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		if ($rest->error !== false) {
			//trigger_error(sprintf("SES::sendEmail(): [%s] %s", $rest->error['code'], $rest->error['message']), E_USER_WARNING);
			return false;
		}
		$rest = objectsIntoArray($rest);
		$response = array('request_id'=>$rest['body']['ResponseMetadata']['RequestId']);
		return $response;
	}

	public static function sendRawEmail($raw_email) {
		$raw_email['RawMessage.Data'] = $raw_email['RawMessage'];
		unset($raw_email['RawMessage']);
		$rest = new SESRequest('POST', 'SendRawEmail', $raw_email);
		$rest = $rest->getResponse();
		if ($rest->error === false && $rest->code !== 200) $rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		if ($rest->error !== false) {
			//trigger_error(sprintf("SES::SendRawEmail(): [%s] %s", $rest->error['code'], $rest->error['message']), E_USER_WARNING);
			return false;
		}
		$rest = objectsIntoArray($rest);
		$response = array('request_id'=>$rest['body']['ResponseMetadata']['RequestId']);
		return $response;
	}

	public static function __getSignature($string) {
		return 'AWS3-HTTPS AWSAccessKeyId='.self::$__accessKey.', Algorithm=HmacSHA1, Signature='.self::__getHash($string);
	}

	private static function __getHash($string) {
		return base64_encode(extension_loaded('hash') ?
		hash_hmac('sha1', $string, self::$__secretKey, true) : pack('H*', sha1(
		(str_pad(self::$__secretKey, 64, chr(0x00)) ^ (str_repeat(chr(0x5c), 64))) .
		pack('H*', sha1((str_pad(self::$__secretKey, 64, chr(0x00)) ^
		(str_repeat(chr(0x36), 64))) . $string)))));
	}
}


final class SESRequest {
	private $verb, $resource = '';
	public $response;

	function __construct($verb, $action = '', $data = false, $defaultHost = 'email.us-east-1.amazonaws.com') {
		$this->data = array('Action'=>$action);
		if ($data) $this->data = array_merge($this->data, $data);
		$this->verb = $verb;
		$this->headers['Host'] = $defaultHost;
		$this->headers['Date'] = gmdate('D, d M Y H:i:s T');
		$this->headers['X-Amzn-Authorization'] = SES::__getSignature($this->headers['Date']);
		$this->resource = '/';

		$this->response = new STDClass;
		$this->response->error = false;
	}

	public function getResponse() {
		$query = '';
		$data = '';
		foreach(array_keys($this->data) as $key) {
			$data .= $key.'='.rawurlencode($this->data[$key]).'&';
		}
		$data = rtrim($data, '&');
		$url = 'https://'.$this->headers['Host'];
		if ($this->verb=='GET') {
			$url .= '?'.$data;
		}
		// Basic setup
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERAGENT, 'SES/php');
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
		if (defined('CERTIFICATE_FILE_PATH')) curl_setopt($curl, CURLOPT_CAINFO, CERTIFICATE_FILE_PATH);

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_HEADERFUNCTION, array(&$this, '__responseHeaderCallback'));
		switch ($this->verb) {
			case 'GET': break;
			case 'POST':
				$this->headers['Content-Type'] = 'application/x-www-form-urlencoded';
				if ($this->data !== false) {
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				}
			break;
			default: break;
		}

		$prepared_headers = array();
		foreach ($this->headers as $header => $value) if (strlen($value) > 0) $prepared_headers[] = $header.': '.$value;
		curl_setopt($curl, CURLOPT_HTTPHEADER, $prepared_headers);
		curl_setopt($curl, CURLOPT_HEADER, false);


		$result = curl_exec($curl);
		if ($result) $this->response->code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		else
			$this->response->error = array(
				'code' => curl_errno($curl),
				'message' => curl_error($curl),
				'resource' => $this->resource
			);

		@curl_close($curl);

		if ($this->response->error === false && isset($this->response->headers['type']) &&
		$this->response->headers['type'] == 'text/xml' && $result!==false) {
			$this->response->body = simplexml_load_string($result);
			if (!in_array($this->response->code, array(200, 204)) &&
			isset($this->response->body->Code, $this->response->body->Message)) {
				$this->response->error = array(
					'code' => (string)$this->response->body->Code,
					'message' => (string)$this->response->body->Message
				);
				if (isset($this->response->body->Resource))
					$this->response->error['resource'] = (string)$this->response->body->Resource;
				unset($this->response->body);
			}
		}

		return $this->response;
	}

	private function __responseHeaderCallback(&$curl, &$data) {
		if (($strlen = strlen($data)) <= 2) return $strlen;
		if (substr($data, 0, 4) == 'HTTP')
			$this->response->code = (int)substr($data, 9, 3);
		else {
			list($header, $value) = explode(': ', trim($data), 2);
			if ($header == 'Last-Modified')
				$this->response->headers['time'] = strtotime($value);
			elseif ($header == 'Content-Length')
				$this->response->headers['size'] = (int)$value;
			elseif ($header == 'Content-Type')
				$this->response->headers['type'] = $value;
			elseif ($header == 'ETag')
				$this->response->headers['hash'] = $value{0} == '"' ? substr($value, 1, -1) : $value;
			elseif (preg_match('/^x-amz-.*$/', $header))
				$this->response->headers[$header] = is_numeric($value) ? (int)$value : $value;
		}
		return $strlen;
	}
}

//Function by: ashok.893@gmail.com
//Posted: April 26, 2010
//Found: Jan. 25, 2011
//Located: http://www.php.net/manual/en/book.simplexml.php#97555
function objectsIntoArray($arrObjData, $arrSkipIndices = array()) {
	$arrData = array();
	if (is_object($arrObjData)) $arrObjData = get_object_vars($arrObjData);
	if (is_array($arrObjData)) {
		foreach ($arrObjData as $index => $value) {
			if (is_object($value) || is_array($value)) $value = objectsIntoArray($value, $arrSkipIndices);
			if (in_array($index, $arrSkipIndices)) continue;
			$arrData[$index] = $value;
		}
	}
	return $arrData;
}

?>
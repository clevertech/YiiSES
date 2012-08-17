<?php
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

/**
*	All code based heavily upon Undesigned Amazon S3 PHP Class located at: http://undesigned.org.za/2007/10/22/amazon-s3-php-class
*
* 	SES.php Usage
*/

if (!class_exists('SES')) require_once 'SES.php';

// AWS access info
if (!defined('awsAccessKey')) define('awsAccessKey', 'change-this');
if (!defined('awsSecretKey')) define('awsSecretKey', 'change-this');


// Check for CURL
if (!extension_loaded('curl') && !@dl(PHP_SHLIB_SUFFIX == 'so' ? 'curl.so' : 'php_curl.dll')) exit("\nERROR: CURL extension not loaded\n\n");

// Pointless without your keys!
if (awsAccessKey == 'change-this' || awsSecretKey == 'change-this')
	exit("\nERROR: AWS access information required\n\nPlease edit the following lines in this file:\n\n".
	"define('awsAccessKey', 'change-me');\ndefine('awsSecretKey', 'change-me');\n\n");

// Instantiate the class
$ses = new SES(awsAccessKey, awsSecretKey);

// List your Verified Email Addresses
//echo "SES::listVerifiedAddresses(): ".print_r($ses->listVerifiedAddresses(), 1)."\n";

//Verify your Email Address
//echo "SES::verifyAddress(): ".print_r($ses->verifyAddress('valid_email_address@example.com'), 1)."\n";

//Delete a Verified Email Address
//echo "SES::deletedVerifiedAddress(): ".print_r($ses->deletedVerifiedAddress('my_verfied_email_address@example.com'), 1)."\n";

// Get your Send Quota
//echo "SES::getSendQuota(): ".print_r($ses->getSendQuota(), 1)."\n";

// Get your Send Statistics
//echo "SES::getSendStatistics(): ".print_r($ses->getSendStatistics(), 1)."\n";


// Compose and Send an Email
/*
$email = array(
	'Destination' => array(
		'ToAddresses'=> array('valid_email_address@example.com'),
		'CcAddresses'=> array(),
		'BccAddresses'=> array()
	),
	'Message' => array(
		'Subject' => array(
			'Data'=>'Test from PHP-SES',
			'Charset'=>'us-ascii'//Not required if is US-ASCII
		),
		'Body'=> array(
			'Html'=>array(
				"Data" => "Hello! <br/> This is an email body <br/> Good Luck.",
				'Charset'=>'us-ascii'//Not required if is US-ASCII
			),
			'Text'=>array(
				"Data" => "Hello! \n This is an email body \n Good Luck.",
				'Charset'=>'us-ascii'//Not required if is US-ASCII
			)
		)
	),
	'ReplyToAddresses' => array('reply_email_address@example.com'),
	'ReturnPath' => 'email_address@example.com',
	'Source' => 'my_verfied_email_address@example.com'
);
*/
//echo "SES::sendEmail(): ".print_r($ses->sendEmail($email), 1)."\n";

// Compose and Send a Raw Email
/*$raw_message = "";
$raw_email = array(
	'RawMessage' => $raw_message,
	'Source' => 'my_verfied_email_address@example.com'
);
*/
//echo "SES::sendRawEmail(): ".print_r($ses->sendRawEmail($raw_email), 1)."\n";
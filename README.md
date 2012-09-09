# YiiSES
The easiest way to provide bulk email services in your applications.

### Overview

***YiiSES*** is a 'nearly' ready to use module that provides our Yii applications with a powerful mail marketing tool.

The system makes use of **[Amazon Simple Email Service (Amazon SES)](http://aws.amazon.com/ses/)**, chosen for its highly [reduced price](http://aws.amazon.com/ses/pricing/), scalability and features. A developer can easily register and use its service at no charge. The only issue is that the developer will only be able to send to verified email addresses only.

This module has been ported to Open Source from one of the projects at [Clevertech](http://clevertech.biz) and we are very happy with its results so far.

### Install
Even though there was some good effort to make it easy to install, its configuration is a bit tricky. We hope this will change with a bit of help from the community.

Within the code you will find two types of configurations, one to be used with [YiiBoilerplate](http://github.com/clevertech/YiiBoilerplate) project structure (default), and the other when we install the module on a Yii's default application structure.

The following instructions are to install the module on a Yii's default application structure.

#### Configuration
* Create your web application `./yiic webapp <folder>`
* Create your **modules** folder under **protected** and unpack the module there
* Place **commands\CampaignCommand.php** on your commands directory and **ses\migrations\..** on the migrations directory. 
* Configure your **config\main.php** and **config\console.php** configuration files (make sure your db is setup correctly -*migrations will only work on mySQL db*):  

	``` 
	// console.php
	…
	// urlManager must be the same configuration as on main.php
	'urlManager' => array(
		// remember to have your .htaccess setup correctly
		'urlFormat' => 'path',
		'showScriptName' => false,
		'urlSuffix' => '/',
		'rules'=>array(
			'<controller:\w+>/<id:\d+>'=>'<controller>/view',
			'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
			'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
		),	
	),
	// request is required to create appropriate urls within the emails
	// when processed with the CampaignCommand.php
	'request' => array(
	)
	…
	
	// main.php
	…
	'modules' => array(
		'ses'=>array(
			'password'=>'clevertech',
		),
	),
	…	
	// urlManager must be the same configuration as on main.php
	'urlManager' => array(
		// remember to have your .htaccess setup correctly
		'urlFormat' => 'path',
		'showScriptName' => false,
		'urlSuffix' => '/',
		'rules'=>array(
			'<controller:\w+>/<id:\d+>'=>'<controller>/view',
			'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
			'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
		),	
	),	
	…
	'params'=> array(
		'ses.aws.key'=>'',
	…
	
	...
	```
* Setup required parameters on your configuration files (must be shared among console.php and main.php):
	* **ses.aws.key**: Your [Amazon SES API Key](http://aws.amazon.com/documentation/ses/)
	* **ses.aws.secret**: Your Amazon SES Secret
	* **ses.aws.verifiedEmail**: You are required to verify at least one email address on Amazon SES console in order to send emails 
	* **ses.aws.test.email**: Email where to send *test* emails. If your account is not in production, then this email should be also verified on Amazon SES console.
	* **ses.emailonacid.key**: If you wish to preview and test your emails on [EmailOnAcid](http://www.emailonacid.com) service, you have to configure your API key here.
	* **ses.emailonacid.pwd**: Your password to access [EmailOnAcid](http://www.emailonacid.com) service for testing and previewing your emails.
	* **ses.files.path**: Set this parameter in order to make use of the image upload capabilities of [RedactorJS](http://redactorjs.com/), our chosen WYSIWYG editor for the module.
	* **ses.files.url**: Required to display the thumbnail gallery of [RedactorJS](http://redactorjs.com/).
	* **ses.panel.login.url**: The Route to the control panel. This parameter is required at the unsubscribe form. If somebody tries to remove an email from a registered user, then it will be presented with the link created from this route, to inform that he should log into its panel in order to unsubscribe. 
	* **ses.from.name**: Your application name. Will be included in the Emails From Name (ie."My Application <verified email address>" )
	
* Make sure your paths are correctly set:
	* CampaignCommand.php::init() **- line  74**
	* CampaignCommand.php::sendEmail() **- line 376**
	* models\Email.php::formatHTMLMessage() **- line 136**
	* models\Template.php::getView **- line 65**  
* Run migrations `./yiic migrate` from your command line prompt.

####The User table and Model
One important part of the configuration is how you deal with the subscribers. The module works by default with the assumptions that your application has a **User** model with **id, email, and username, subscribed** attributes. 

The module comes with a single field for campaign that configures whether to send to subscribers or not but you can easily change that behavior to match different fields or subscription option from your registered members *-but this is something that I won't explain here :)*.

###Requirements
- [EPHPThumb](http://www.yiiframework.com/extension/ephpthumb)
- [SES Component](https://github.com/zshannon/Amazon-Simple-Email-Service-PHP)  
- [YiiBootstrap](http://www.yiiframework.com/extension/bootstrap)  
- [HighCharts](http://www.highcharts.com/)  
- [RedactorJS](http://www.redactorjs.com/)

###License  
[![License](http://i.creativecommons.org/l/by-sa/3.0/88x31.png)](http://creativecommons.org/licenses/by-sa/3.0/)  
This work is licensed under a [Creative Commons Attribution-ShareAlike 3.0 Unported License](http://creativecommons.org/licenses/by-sa/3.0/)  

====

> [![Clevertech](http://clevertech.biz/images/slir/w54-h36-c54:36/images/site/index/home/clevertech-logo.png)](http://www.clevertech.biz)    
well-built beautifully designed web applications  
[www.clevertech.biz](http://www.clevertech.biz)


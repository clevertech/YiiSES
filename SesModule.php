<?php
/**
 * Email Module
 *
 * Configuration settings at common/config/main.php
 * ---------------------------
 * amazon simple email service
 * ---------------------------
 * aws.ses.key          < Amazon API Key credentials >
 * aws.ses.secret       < Amazon API Secret credentials >
 * aws.ses.verifiedEmail'=>'hello@projectdecor.com' < This email requires to be verified by AWS >
 *
 * Configuration settings at main/config/main.php
 * ---------------------------
 * modules
 * ---------------------------
 * 'modules' => array(
 *      'email'...
 * ---------------------------
 * emailonacid API widget
 * ---------------------------
 * ses.emailonacid.key      < Emailonacid.com API Key credentials >
 * ses.emailonacid.pwd      < Emailonacid.com API Password credentials >
 *
 *
 * Basic usage
 *
 * - Campaigns can be created/edited and its HTML and Txt body validated via emailonacid services.
 *
 * - A campaign status can be changed to be processed via its status: READY TO BE SEND, flags the campaign to start
 *    processing it and enqueue the emails on campaign_email table to the users who wish to receive the type processed.
 *
 * - The enqueue of emails and further process are via the CampaignCommand located at console/commands. Its functionality
 *    is explained in its file.
 *
 *
 */
class SesModule extends CWebModule
{

	protected $_assetsUrl;

	public $layout = 'ses.views.layouts.main';

	public $bootstrap;

	public $defaultController = 'campaign';

	public $useOwnLogin = true;
	/**
	 * @var string the password that can be used to access GiiModule.
	 * If this property is set false, then GiiModule can be accessed without password
	 * (DO NOT DO THIS UNLESS YOU KNOW THE CONSEQUENCE!!!)
	 */
	public $password;

	public function init()
	{
		parent::init();


		Yii::import('ses.models.*');
		Yii::import('ses.components.helpers.*');
		Yii::import('ses.behaviors.*');

		Yii::setPathOfAlias('bootstrap', Yii::getPathOfAlias('ses.extensions.bootstrap'));

		$components = array(
			'bootstrap' => array(
				'class' => 'bootstrap.components.Bootstrap',
				'responsiveCss'=>true
			)
		);

		if($this->useOwnLogin)
		{
			/* this is happening only when using own login  */
			$components = CMap::mergeArray(array(
				'errorHandler' => array(
					'class' => 'CErrorHandler',
					'errorAction' => $this->getId() . '/default/error',
				),
				'user' => array(
					'class' => 'CWebUser',
					'stateKeyPrefix' => 'ses',
					'loginUrl' => Yii::app()->createUrl($this->getId() . '/default/login'),
				),
				'widgetFactory' => array(
					'class' => 'CWidgetFactory',
					'widgets' => array()
				)
			), $components);
		}
		Yii::app()->setComponents($components, false);
		/* reference to the object will fire its init() and register css and js files*/
		Yii::app()->bootstrap;
	}

	public function beforeControllerAction($controller, $action)
	{

		/* if request is any of our public actions return true */
		if (parent::beforeControllerAction($controller, $action))
		{
			$route = $controller->id . '/' . $action->id;

			$publicPages = array(
				'default/login',
				'default/error',
				'message/image',
				'unsubscribed/form',
			);
			if (Yii::app()->user->isGuest && !in_array($route, $publicPages))
				Yii::app()->user->loginRequired();
			else
				return true;
		}
		return false;
	}

	/**
	 * getAssetsUrl
	 *
	 * @access public
	 * @return void
	 */
	public function getAssetsUrl()
	{
		if ($this->_assetsUrl === null)
		{
			$this->_assetsUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('ses.assets'), false, -1, YII_DEBUG);
		}

		return $this->_assetsUrl;
	}
}

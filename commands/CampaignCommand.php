<?php
/**
 * CampaignCommand class
 *
 * Description: Process campaigns created by module email.
 *
 * Does have three actions that need to be setup as CRON JOBS:
 *  ./yiic campaign enqueue  // queues emails for its further process. Works with "send" command.
 *  ./yiic campaign send   // sends queued emails, to be set toghether with "enqueue" command
 *  ./yiic campaign boost // if you set this one, the others are not needed. emails are processed and send immediately
 *
 * Enqueue action -
 * Queries campaigns flagged to be READY to enqueue messages to 'campaign_email' table.
 *
 * Send action -
 * Process messages on 'campaign_email' table and sends them using Amazon Simple Email Service.
 *
 * Notes.
 * In order to use Amazon SES, we need to sign up to:
 * https://aws-portal.amazon.com/gp/aws/developer/registration/index.html
 *
 * Once, we have an account, we can go to the console to verify a couple of emails as on 'sandbox mode' we cannot use
 * any other emails for our tests.
 *
 * Also, we need to configure the following parameters on main.php
 *
 * 'ses.aws.key'=>'xxxxxxxxxx',
 * 'ses.aws.secret'=>'xxxxxxxxxx',
 * 'ses.aws.verifiedEmail'=>'hello@projectdecor.com', <-- this must be our verifiedEmail sender
 *
 * That you can find once logged in on the AWS console at the following address:
 * https://aws-portal.amazon.com/gp/aws/securityCredentials
 *
 * Once tested we can request to go on production mode on:
 * https://console.aws.amazon.com/ses/home
 *
 * @author: Antonio Ramirez <antonio@clevertech.biz>
 */
class CampaignCommand extends CConsoleCommand
{
	/**
	 * @var $_verbose if -v will display messages
	 */
	private $_verbose;

	/**
	 * @var $_test if -t will work on 'TEST' mode
	 */
	private $_test;

	/**
	 * @var if set will send emails on 'TEST' mode
	 */
	private $_force;

	/**
	 * @var $_isCli if is runned on console or not
	 */
	private $_isCli;

	/**
	 * @var SES component
	 */
	private $_ses;

	/**
	 * Initializes command _isCli variable to find out whether is runned on console or not
	 */
	public function init()
	{
		// import required classes
		// make sure path alias is correct!
		// The following is to work with YiiBoilerplate @see http://github.com/clevertech/YiiBoilerplate
		 Yii::import('root.backend.modules.ses.models.*');
		 Yii::import('root.backend.modules.ses.behaviors.*');
		 Yii::import('root.backend.modules.ses.models.Email');
		 Yii::import('root.backend.modules.ses.components.ses.SES');
		 Yii::import('root.backend.modules.ses.components.helpers.*');
		// Yii's default web app
		// Yii::import('application.modules.ses.models.*');
		// Yii::import('application.modules.ses.behaviors.*');
		// Yii::import('application.modules.ses.models.Email');
		// Yii::import('application.modules.ses.components.ses.SES');
		// Yii::import('application.modules.ses.components.helpers.*');


		$this->_isCli = php_sapi_name() === 'cli';

		parent::init();
	}

	/**
	 * @var string $params the global reference to the Google Analytics variables
	 * @see http://support.google.com/analytics/bin/answer.py?hl=en&answer=1033867
	 *
	 */
	protected $params;

	/**
	 * Process emails on queue at campaign_email table and sends them via AWS SES
	 */
	public function actionSend($args)
	{
		// get arguments
		$this->_verbose = is_array($args) && in_array('-v', $args);
		$this->_test = is_array($args) && in_array('-t', $args); // force
		$this->_force = is_array($args) && in_array('-f', $args); // force send?

		$db = Yii::app()->db;

		// Emails are sent 100 at a time <be careful when changing that setting>
		$emails = $db->createCommand('SELECT * FROM campaign_email WHERE status=' .
			($this->_test ? Email::STATUS_TEST : Email::STATUS_DRAFT) .
			' ORDER BY create_time LIMIT 100')->queryAll();

		$failed = $sent = $campaign_id = 0;

		$this->printLine('"' . count($emails) . '" emails to send.');

		foreach ($emails as $email)
		{
			if ($campaign_id != $email['campaign_id'])
			{
				if ($campaign_id != 0 && ($sent != 0 || $failed != 0))
				{
					$this->updateCampaignStats($campaign_id, $sent, $failed);
				}
				// update campaign id
				$campaign_id = $email['campaign_id'];
				// reset stats
				$failed = $sent = 0;
			}
			// uncomment if you wish to display a message per email (if you have thousand+ not recommended) on test mode
			// $this->printLine('email to "' . $email["to_address"] . '" to be send.');
			// send mail
			if ($this->sendEmail($email))
			{
				++$sent;
			} else
			{
				++$failed;
			}
		}
		/**
		 * update campaign stats
		 */
		if ($campaign_id != 0 && ($sent != 0 || $failed != 0))
		{
			$this->updateCampaignStats($campaign_id, $sent, $failed);

			$this->printLine('Emails Sent: ' . $sent . '<br/>Emails Failed: ' . $failed . '<br/>Total Emails: ' . ($failed + $sent));
		}
	}

	/**
	 * Process campaigns ready to be send, queues emails and sends them immediately
	 * @param $args
	 */
	public function actionBoost($args)
	{
		$this->_verbose = is_array($args) && in_array('-v', $args);
		$this->_test = is_array($args) && in_array('-t', $args);
		$this->_force = is_array($args) && in_array('-f', $args);

		// get all campaigns
		$campaigns = Yii::app()->db->createCommand('SELECT * FROM campaign WHERE status=' .
			($this->_test ? Campaign::STATUS_TEST : Campaign::STATUS_READY) .
			' AND scheduled_for <= ' . time() . ' LIMIT 2')->queryAll();

		foreach ($campaigns as $campaign)
		{
			$failed = $sent = 0;
			try
			{
				Campaign::model()->updateByPk($campaign['id'], array('status' => Campaign::STATUS_PROCESSING));

				$users = $this->getUsers($campaign);

				if (empty($users))
				{
					Campaign::model()->updateByPk($campaign['id'], array('status' => Campaign::STATUS_ERROR));
					continue;
				}
				// loop through users  (if any)
				foreach ($users as $user)
				{
					if ($this->enqueueAndSend($campaign, $user))
					{
						++$sent;
					} else
					{
						++$failed;
					}
				}
				$this->updateCampaignStats($campaign['id'], $sent, $failed);

				$this->printLine('"' . $campaign['name'] . '" processed and its emails queued and sent.');

				if (!$this->_isCli)
				{
					$this->printLine('<a href="#" data-id="' .
						$campaign['id'] . '" class="status-reset" >set "' .
						$campaign['name'] . '" back to DRAFT status</a>');
				}

				Campaign::model()->updateByPk($campaign['id'], array('status' => Campaign::STATUS_SENT));

			} catch (Exception $e)
			{
				// an error occurred, update status
				Campaign::model()->updateByPk($campaign['id'], array('status' => Campaign::STATUS_ERROR));

				$this->printLine('"' . $campaign['name'] . '" not processed. An error occurred: ' . $e->getMessage());
				// write to log
				Yii::log(strtr('An unexpected error has occurred processing CampaignCommand: {msg}',
					array('{msg}' => $e->getMessage())), CLogger::LEVEL_ERROR);
			}
		}
	}

	/**
	 *
	 * Process campaigns flagged as ready, and queues them by saving them to database for later processing via the
	 * send action
	 */
	public function actionEnqueue($args)
	{
		$this->_verbose = is_array($args) && in_array('-v', $args);
		$this->_test = is_array($args) && in_array('-t', $args);

		$campaigns = Yii::app()->db->createCommand('SELECT * FROM campaign WHERE status=' .
			($this->_test ? Campaign::STATUS_TEST : Campaign::STATUS_READY) .
			' AND scheduled_for <= ' .
			time() .
			' LIMIT 2')->queryAll();

		$cmd = Yii::app()->db->createCommand();

		foreach ($campaigns as $campaign)
		{
			try
			{
				$this->printLine('"' . $campaign["name"] . '" to be processed.');
				$cmd->update('campaign', array('status' => Campaign::STATUS_PROCESSING), 'id=:campaign_id', array(':campaign_id' => $campaign['id']));

				$users = $this->getUsers($campaign);

				if (empty($users))
				{
					continue;
				}
				// loop through users
				foreach ($users as $user)
				{
					$this->enqueueAndSend($campaign, $user, false);
				}

				// update campaign status
				$cmd->update('campaign', array('status' => Campaign::STATUS_SENT), 'id=:campaign_id', array(':campaign_id' => $campaign['id']));

				$this->printLine('"' . $campaign['name'] . '" processed and its emails queued.');

				if (!$this->_isCli)
				{
					$this->printLine('<a href="#" data-id="' . $campaign['id'] . '" class="status-reset" >set "' . $campaign['name'] . '" back to DRAFT status</a>');
				}

			} catch (Exception $e)
			{
				// an error occurred, update status
				$cmd->update('campaign', array('status' => Campaign::STATUS_ERROR), 'id=:campaign_id', array(':campaign_id' => $campaign['id']));

				$this->printLine('"' . $campaign['name'] . '" not processed. An error occurred: ' . $e->getMessage());
				// write to log
				Yii::log(strtr('An unexpected error has occurred processing CampaignCommand: {msg}', array('{msg}' => $e->getMessage())), CLogger::LEVEL_ERROR);
			}
		}
	}

	/**
	 * Saves emails to queue and sends them if specified by parameter
	 * @param $campaign the campaign to process
	 * @param $user the user attributes
	 * @param bool $send whether to send or not
	 * @return bool
	 */
	private function enqueueAndSend($campaign, $user, $send = true)
	{
		// replace tokens, links, and format body html appropriately
		$bodyHtml = Email::formatHTMLMessage($this->parseBody($campaign['body_html'], $user),
			Template::getView($campaign['template_id']), $user['id']);

		// replace tokens and links
		$bodyText = $this->parseBody($campaign['body_text'], $user, false);

		// save email to DB for its process with command
		$email = Email::model()->enqueue(
			$campaign['id'], $campaign['subject'], $bodyText, $user['email'], $user['email'], // you can use for name something different!
			$this->getVerifiedEmail(), $this->getVerifiedEmail(), $bodyHtml,
			($this->_test ? Email::STATUS_TEST : Email::STATUS_DRAFT));

		if (!$email) // this happens when email fails to be saved
		{
			// uncomment if you wish to display a message per email (if you have thousand+ not recommended) on test mode
			// $this->printLine('"' . $user["email"] . '" failed to be queued.');
			return false;
		}
		// uncomment if you wish to display a message per email (if you have thousand+ not recommended) on test mode
		// $this->printLine('"' . $user["email"] . ($send ? '" sent.' : '" queued.'));

		return $send ? $this->sendEmail($email) : true;
	}

	/**
	 * Updates the stats data of a campaign
	 * @param $id
	 * @param $sent
	 * @param $failed
	 * @return int
	 */
	private function updateCampaignStats($id, $sent, $failed)
	{

		// uncomment the following and comment next to use CB instead of DAO
		/*return Yii::app()->db
			->commandBuilder
			->createUpdateCounterCommand('campaign', array(
			'total_list' => ($sent + $failed),
			'total_sent' => $sent,
			'total_failed' => $failed
		), new CDbCriteria(array(
			'condition' => 'id=:id',
			'params' => array(
				':id' => $id
			))))
			->execute();*/

		// uncomment if you wish to use DAO
		return Yii::app()->db->createCommand(
			'UPDATE campaign SET total_list=total_list+:total, total_sent=total_sent+:sent, total_failed=total_failed+:failed, sent_at=NOW() WHERE id=:id')
			->bindValues(array(
			':total' => $failed + $sent,
			':sent' => $sent,
			':failed' => $failed,
			':id' => $id
		))->execute();
	}

	/**
	 * Sends an email
	 * @param $email
	 * @return bool
	 */
	private function sendEmail($email)
	{

		$ses = $this->getSES();
		$attrs = $email instanceof CActiveRecord ? $email->getAttributes() : $email;

		Email::model()->updateByPk($email['id'], array('status' => Email::STATUS_PREPARE));

		$message = array(
			'Destination' => array(
				'ToAddresses' => array($attrs['to_address'])
			),
			'Message' => array(
				'Subject' => array(
					'Data' => $attrs['subject'],
					'Charset' => 'utf-8'
				),
				'Body' => array(
					'Html' => array(
						/* replace token for image that will open and display the stats */
						/* make sure it points to the right url  (this is the setup when working with YiiBoilerplate) */
						/* @see http://github.com/clevertech/YiiBoilerplate  */
						"Data" => strtr($attrs['html_body'], array('--IMGSTATS--' => Yii::app()->createAbsoluteUrl('/backend/www/ses/message/image', array('id' => $attrs['id'])))),
						/* Yii's webapp defaults */
						// "Data" => strtr($attrs['html_body'], array('--IMGSTATS--' => Yii::app()->createAbsoluteUrl('message/image', array('id' => $attrs['id'])))),
						'Charset' => 'utf-8'
					),
					'Text' => array(
						"Data" => $attrs['text_body'],
						'Charset' => 'utf-8'
					)
				)
			),
			'ReplyToAddresses' => array($attrs['from_address']),
			'ReturnPath' => $attrs['from_address'],
			'Source' => @Yii::app()->params['ses.from.name'].' <' . $this->getVerifiedEmail() . '>'
		);

		if ($this->_test && !$this->_force)
		{
			$status = Email::STATUS_DELETED; /* update to deleted so it is not used again after test mode */
			// I would comment this one but, lets keep it for test mode
			$this->printLine('email to "' . $attrs["to_address"] . '" about to be send (won\'t send email though).');
		} else
		{
			if ($ses->sendEmail($message))
			{
				$status = Email::STATUS_SENT;
				// uncomment if you wish to display a message per email (if you have thousand+ not recommended) on test mode
				// $this->printLine('email to "' . $attrs["to_address"] . '" sent.');

			} else
			{
				$status = Email::STATUS_FAILED;
				// uncomment if you wish to display a message per email (if you have thousand+ not recommended) on test mode
				// $this->printLine('email to "' . $attrs["to_address"] . '" failed.');
			}
		}

		if ($this->_test)
		{
			$status = Email::STATUS_DELETED;
		}

		Email::model()->updateByPk($attrs['id'], array('send_time' => time(), 'status' => $status));

		return ($status == Email::STATUS_SENT || ($status == Email::STATUS_DELETED && $this->_test));
	}

	/**
	 * Returns SES component
	 * @return mixed
	 */
	private function getSES()
	{
		if (null === $this->_ses)
		{
			$this->_ses = new SES(Yii::app()->params['ses.aws.key'], Yii::app()->params['ses.aws.secret']);
		}
		return $this->_ses;
	}

	/**
	 * Returns verified email to send campaigns
	 * @return mixed
	 */
	private function getVerifiedEmail()
	{
		return Yii::app()->params['ses.aws.verifiedEmail'];
	}

	/**
	 * Returns the users selected by a specified campaign
	 * @param $campaign
	 * @return array
	 */
	private function getUsers($campaign)
	{
		$userCommand = null;
		$conditions = '';
		$notIn = $params = $custom_users = $sql_users = array();

		$this->printLine('"' . $campaign["name"] . '" to be processed.');

		Campaign::model()->updateByPk($campaign['id'], array('status' => Campaign::STATUS_PROCESSING));

		$this->params = http_build_query(array(
			'utm_campaign' => $campaign['name'],
			'utm_term' => $campaign['utm_term'],
			'utm_medium' => $campaign['utm_medium'],
			'utm_source' => $campaign['utm_source'],
			'utm_content' => $campaign['utm_content']
		));

		/**
		 * Get selected users -if any
		 */
		if ($campaign['selected_users'])
		{
			$emails = explode(',', $campaign['selected_users']);
			foreach ($emails as $email)
			{
				$email = trim($email);

				if (strlen($email))
				{
					$user = User::model()->findByAttributes(array('email' => $email));
					if (!$user)
					{
						continue;
					}
					$notIn[] = $email;
					$custom_users[] = array('id' => $user->id, 'email' => $email);
				}
			}
		}

		/**
		 * Get custom users -if any
		 */
		if ($campaign['custom'])
		{
			$emails = explode(',', $campaign['custom']);

			foreach ($emails as $email)
			{
				$email = trim($email);

				// make sure we got an email and is not within the selected ones already
				if (strlen($email) && !in_array($email, $notIn))
				{
					if (!Unsubscribed::model()->findByAttributes(array('email' => $email)))
					{
						$notIn[] = $email;
						$custom_users[] = array('id' => null, 'email' => $email);
					}
				}
			}
		}

		if ($campaign['to_subscribers'])
		{
			$userCommand = Yii::app()->db->createCommand()
				->select('id, email')
				->from('user');

			$conditions = 'subscribed=1';
		}

		if ($userCommand === null && !count($custom_users))
		{
			/* avoiding the sending to all users at least one option should be applied*/
			Campaign::model()->updateByPk($campaign['id'], array('status' => Campaign::STATUS_ERROR));

			$this->printLine('"' . $campaign['name'] . '" not processed: no emails selected.');
			return array();

		} elseif ($userCommand !== null)
		{
			if (count($notIn))
			{
				$this->printLine(count($notIn) . " emails from selected and custom to be queued.");
				$conditions .= ' AND email NOT IN ("' . implode('","', $notIn) . '") AND email NOT IN (SELECT email FROM campaign_unsubscribed)';

			} else
			{
				$conditions .= ' AND email NOT IN (SELECT email FROM campaign_unsubscribed)';
			}

			$sql_users = $userCommand->where($conditions)->queryAll();

			//
			$this->printLine('Parameters: ' . CVarDumper::dumpAsString($params));
			$this->printLine($userCommand->text);
			$this->printLine(count($sql_users) . " emails from SQL command.");
		}


		/* merge result dataset */
		return array_merge($sql_users, $custom_users);
	}

	/**
	 * Parses text/HTML replacing tokens for their correspondent user specific attributes and appends Google Analytics
	 * parameters to every URL or HREF on the text/HTML
	 *
	 * @param $body the content to parse
	 * @param $user User Model from whom to get the attributes that will replace tokens found
	 * @param bool $isHtml whether the $body content is text or HTML code
	 * @return mixed parsed content
	 */
	private function parseBody($body, $user, $isHtml = true)
	{
		$rep = Campaign::model()->tokens;

		// be careful with replacement tokens
		// make sure the attributes exists on user
		// @see Campaign::model()->tokens
		foreach (Campaign::model()->tokens as $key => $attribute)
		{
			$rep[$key] = $user[$attribute];
		}

		$body = strtr($body, $rep);

		$reg = $isHtml ? '/href=[\'"]+?\s*(?P<link>\S+)\s*[\'"]+?/i' : '/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/i';

		return preg_replace_callback($reg,
			array($this, 'processLinks'), $body);

	}

	/**
	 * Process links found by preg_replace_callback
	 * @see parseBody
	 * @param $matches
	 * @return string
	 */
	private function processLinks($matches)
	{
		$key = isset($matches['link']) ? 'link' : 0;


		$link = preg_match('/\\?[^"]/', $matches[$key]) ?
			$matches[$key] . '&' . $this->params :
			$matches[$key] . '?' . $this->params;


		return isset($matches['link']) ? 'href="' . $link . '"' : $link;

	}

	/**
	 * Helper function to display process information to the console or browser.
	 * @param string $line the line to echo
	 */
	private function printLine($line)
	{
		if ($this->_verbose)
		{
			if ($this->_isCli)
			{
				echo "\033[31m" . $line . "\033[0m";
				echo PHP_EOL . '..........................' . PHP_EOL;
			} else
			{
				echo "<p>$line</p>";
				echo "<p>..........................</p>";
			}
		}
	}

	/**
	 * @return string help information
	 */
	public function getHelp()
	{
		return <<<EOD
USAGE
  yiic campaign send
  yiic campaign enqueue
  yiic campaign boost

DESCRIPTION
  yiic campaign enqueue This command stores emails on campaign_email table from campaigns flagged as 'ready to send'.
  yiic campaign send This command sends the emails currently stored in campaign_email table.
  yiic campaign boost This command stores emails on campaign_email table from campaigns flagged as 'ready to send' and sends them immediately.
  It also cleans up old sent emails.

EOD;
	}
}

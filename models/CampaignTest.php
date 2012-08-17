<?php
/**
 * CampaignTest
 *
 * Tests email enqueue and format of the CampaignCommand.php
 * @see CampaignController actionTest
 *
 */
class CampaignTest
{

	const SECRET_KEY = 'mysecretkey'; // replace with your secret key
	/**
	 * Tests enqueue command (yiic campaign enqueue)
	 * @static
	 */
	public static function testEnqueueCommand()
	{


		echo 'Running Enqueue Command: <br /><br />';

		$runner = new CConsoleCommandRunner;
		$runner->addCommands(Yii::getPathOfAlias('ses.commands'));

		echo '<pre>';
		$start = microtime(true);
		$runner->run(array('yiic', 'campaign', 'enqueue', '-v', '-t'));
		$end = microtime(true);
		echo 'Script executed on ' . round($end - $start, 4) . 'ms';
		echo '</pre>';

		echo 'Enqueue command done.<br/>';

		$emails = Yii::app()->db->createCommand('SELECT COUNT(*) FROM campaign_email WHERE status=:status')
			->bindValue(':status', Email::STATUS_TEST)
			->queryScalar();

		echo 'Number of emails queued (varies according to the number of campaigns mark as ready to send) : ' . $emails . '<br/>';
	}

	/**
	 * Tests Send command (yiic campaign send)
	 * @static
	 *
	 */
	public static function testSendCommand($forceSending = false)
	{

		echo 'Running Enqueue Command: <br /><br />';

		$runner = new CConsoleCommandRunner;
		$runner->addCommands(Yii::getPathOfAlias('ses.commands'));

		echo '<pre>';
		$start = microtime(true);
		$args = array('yiic', 'campaign', 'send', '-v', '-t');
		if ($forceSending)
			$args[] = '-f';
		$runner->run($args);
		$end = microtime(true);
		echo 'Script executed on ' . round($end - $start, 4) . 'ms';
		echo '</pre>';

		echo 'Campaign Send Command done.<br/>';

	}

	/**
	 * Removes queued emails that are not of status SENT (@see Email::deleteQueued)
	 * @static
	 * @param $id
	 */
	public static function deleteQueued($id)
	{
		echo 'Running delete command: ' . Yii::app()->params['environment'] . '<br />';

		echo '<pre>';
		$start = microtime(true);
		$emails = Email::deleteQueued($id);
		$end = microtime(true);
		echo 'Script executed on ' . round($end - $start, 4) . 'ms';
		echo '</pre>';

		echo 'Campaign Delete Queued Command done.<br/>';
		echo 'Number of emails removed from queue (only those on TEST or DRAFT, never SENT): ' . $emails . '<br/>';

	}

	/**
	 * Displays the button links for testing the campaign
	 * @static
	 */
	public static function displayTestLinks()
	{
		/** @var $links CHANGE 'mysecretkey' to the one you wish to use for testing */
		$links = array(
			CHtml::link('Reset a Campaign to DRAFT Status',
				Yii::app()->createUrl('ses/campaign/testCommand',
					array('secret_key' => self::SECRET_KEY, 'action' => 'r')),
				array('class' => 'link-reset btn', 'style' => 'margin-top:5px')),
			CHtml::link('Delete Queued Emails of a Campaign',
				Yii::app()->createUrl('ses/campaign/testCommand',
					array('secret_key' => self::SECRET_KEY, 'action' => 'd')),
				array('class' => 'link-queued btn', 'style' => 'margin-top:5px')),
			CHtml::link('Test Queue Command',
				Yii::app()->createUrl('ses/campaign/testCommand',
					array('secret_key' => self::SECRET_KEY, 'action' => 'q')),
				array('class' => 'btn', 'style' => 'margin-top:5px')),
			CHtml::link('Test Send Command',
				Yii::app()->createUrl('ses/campaign/testCommand',
					array('secret_key' => self::SECRET_KEY, 'action' => 's')),
				array('class' => 'link-send btn', 'title' => 'send command', 'style' => 'margin-top:5px')),
			CHtml::link('Test Send Command (Force SES Delivery)',
				Yii::app()->createUrl('ses/campaign/testCommand',
					array('secret_key' => self::SECRET_KEY, 'action' => 'f')),
				array('class' => 'link-send btn', 'title' => 'send command', 'style' => 'margin-top:5px'))
		);

		echo implode(' ', $links);
	}

	/**
	 * Resets a specific campaign to draft
	 * @static
	 * @param $id
	 */
	public static function resetToDraft($id)
	{
		$campaign = Campaign::model()->findByPk($id);

		if (!$campaign)
		{
			echo 'No Campaign found with id: "' . $id . '"';
		} else
		{
			$campaign->status = Campaign::STATUS_DRAFT;

			if ($campaign->save())
			{
				echo 'Status successfully changed';
			} else
			{
				echo 'Unable to reset the status of campaign!<br/>';
				echo CHtml::link('Send Queued',
					Yii::app()->createUrl('ses/campaign/testCommand',
						array('secret_key' => self::SECRET_KEY, 'action' => 'r')), // remember my
					array('class' => 'link-send', 'title' => 'reset command'));
			}
		}
	}

	/**
	 * Sends a unique test mail to email at aws.ses.test.email parameter
	 * Please note that when on 'test' period on Amazon SES you must have that email to be a verified one
	 * @param $campaign
	 * @return bool
	 */
	public function sendUniqueTest($campaign)
	{
		Yii::import('ses.components.ses.SES', true);
		Yii::import('ses.models.Email', true);

		$ses = new SES(Yii::app()->params['ses.aws.key'], Yii::app()->params['ses.aws.secret']);

		// please, make sure that parameters are set and the $user array is filled with the
		// correspondent data for replacement tokens to take in place.
		// @see Campaign::model()->tokens <- *important*
		$user = array('email'=>trim(Yii::app()->params['ses.aws.test.email']));

		$this->params = $campaign->getGAParams();

		$from = trim(Yii::app()->params['ses.aws.verifiedEmail']);

		$bodyHtml = Email::formatHTMLMessage($this->_parseBody($campaign->body_html, $user), Template::getView($campaign->template_id));
		$bodyText = $this->_parseBody($campaign->body_text, $user, false);

		$message = array(
			'Destination' => array(
				'ToAddresses' => array($user['email'])
			),
			'Message' => array(
				'Subject' => array(
					'Data' => $campaign->subject,
					'Charset' => 'utf-8'
				),
				'Body' => array(
					'Html' => array(
						"Data" => strtr($bodyHtml, array('--IMGSTATS--' => Yii::app()->createAbsoluteUrl('/ses/message/image', array('id' => 0)))),
						'Charset' => 'utf-8'
					),
					'Text' => array(
						"Data" => $bodyText,
						'Charset' => 'utf-8'
					)
				)
			),
			'ReplyToAddresses' => array($from),
			'ReturnPath' => $from,
			'Source' => $from
		);

		return $ses->sendEmail($message);
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
	public function _parseBody($body, $user, $isHtml = true)
	{
		$rep = Campaign::model()->tokens;

		foreach (Campaign::model()->tokens as $key => $attribute)
		{
			$rep[$key] = $user[$attribute];
		}

		$body = strtr($body, $rep);

		$reg = $isHtml ? '/href=[\'"]+?\s*(?P<link>\S+)\s*[\'"]+?/i' : '/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/i';

		return preg_replace_callback($reg,
			array($this, '_processLinks'), $body);

	}

	/**
	 * Process links found by preg_replace_callback
	 * @see _parseBody
	 * @param $matches
	 * @return string
	 */
	private function _processLinks($matches)
	{
		$key = isset($matches['link']) ? 'link' : 0;


		$link = preg_match('/\\?[^"]/', $matches[$key]) ?
			$matches[$key] . '&' . $this->params :
			$matches[$key] . '?' . $this->params;


		return isset($matches['link']) ? 'href="' . $link . '"' : $link;

	}

}

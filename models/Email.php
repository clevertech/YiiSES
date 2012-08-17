<?php

/**
 * This is the model class for table "campaign_email".
 *
 * The followings are the available columns in table 'campaign_email':
 * @property integer $id
 * @property integer $campaign_id
 * @property integer $status
 * @property string $from_address
 * @property string $from_name
 * @property string $to_address
 * @property string $to_name
 * @property string $subject
 * @property string $text_body
 * @property string $html_body
 * @property integer $create_time
 * @property integer $send_time
 */
class Email extends CActiveRecord
{
	const STATUS_DELETED = 0;
	const STATUS_DRAFT = 1;
	const STATUS_SENT = 2;
	const STATUS_FAILED = 3;
	const STATUS_PREPARE = 4;
	const STATUS_TEST = 5;

	/**
	 * Returns the static model of the specified AR class.
	 * @return Email the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'campaign_email';
	}

	/**
	 * Saves its creation time to database
	 * @return bool
	 */
	public function beforeSave()
	{
		if (parent::beforeSave()) {
			if ($this->isNewRecord)
				$this->create_time = time();
			return true;
		}
		return false;
	}

	/**
	 * Rules
	 * @return array
	 */
	public function rules()
	{
		return array(
			array('id, campaign_id, id, from_address, from_name, to_address, to_name, subject, text_body, html_body, create_time, send_time, opened', 'safe', 'on' => 'search'),
		);
	}

	/**
	 * Saves formatted email messages to database for later processing
	 * @static
	 * @param $campaign_id
	 * @param $subject
	 * @param $body
	 * @param $toAddress
	 * @param null $toName
	 * @param null $fromAddress
	 * @param null $fromName
	 * @param null $htmlBody
	 * @return bool
	 */
	public static function enqueue($campaign_id, $subject, $body, $toAddress, $toName = null, $fromAddress = null, $fromName = null, $htmlBody = null, $status = self::STATUS_DRAFT)
	{
		$model = new self;
		$model->campaign_id = $campaign_id;

		if ($fromAddress === null) {
			$model->from_address = Yii::app()->params['ses.aws.verifiedEmail'];
			$model->from_name = Yii::app()->params['ses.aws.verifiedEmail'];
		} else {
			$model->from_address = $fromAddress;
			$model->from_name = $fromName;
		}
		$model->to_address = $toAddress;
		$model->to_name = $toName;
		$model->subject = $subject;
		$model->text_body = $body;
		$model->html_body = empty($htmlBody) ? $body : $htmlBody;
		$model->status = $status;

		return $model->save() ? $model : false;
	}

	/**
	 * Remove queued emails from a specific campaign id (only those not of status SENT that are part of stats data)
	 * @static
	 * @param null $campaign_id
	 * @return int
	 */
	public static function deleteQueued($campaign_id=null)
	{
		$criteria = new CDbCriteria();
		$criteria->addCondition('status!='.self::STATUS_SENT);
		if($campaign_id != null)
		{
			$criteria->compare('campaign_id', $campaign_id);
		}
		return self::model()->deleteAll($criteria);
	}

	/**
	 * Formats the email message with proper rendering for its correct sending
	 * @static
	 * @param $content
	 * @return string
	 */
	public static function formatHTMLMessage($content, $view, $userId=null)
	{
		$params = $userId? array('token'=>Unsubscribed::getUnsubscribeToken($userId)) : array();

		// this should have the base URL for the site, but our test logo is in the assets folder
		$siteUrl = Yii::app()->params['baseUrl'];
		// make sure on your settings display the correct url (this is the setup when working with YiiBoilerplate)
		$unsubscribeUrl = Yii::app()->createAbsoluteUrl('backend/www/ses/unsubscribed/form', $params);
		// Yii default webapp setup
		// $unsubscribeUrl = Yii::app()->createAbsoluteUrl('ses/unsubscribed/form', $params);
		ob_start();
		ob_implicit_flush(false);
		require($view);
		return ob_get_clean();
	}

	/**
	 * Property to return labelled status, mainly used on the grid
	 * @return string
	 */
	public function getLabelledStatus()
	{
		$html = '<span class="badge badge-{rep}">{status}</span>';

		$rep = array(
			self::STATUS_DELETED => array('error', Yii::t('ses', 'Deleted')),
			self::STATUS_DRAFT => array('info', Yii::t('ses', 'Draft')),
			self::STATUS_SENT => array('success', Yii::t('ses', 'Sent')),
			self::STATUS_FAILED => array('warning', Yii::t('ses', 'Failed')),
			self::STATUS_PREPARE => array('important', Yii::t('ses', 'Processing'))
		);

		return strtr($html, array('{rep}' => @$rep[$this->status][0], '{status}' => @$rep[$this->status][1]));
	}

	/**
	 * Search function
	 * @see views/stats.php
	 * @return CActiveDataProvider
	 */
	public function search()
	{
		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('campaign_id', $this->campaign_id);
		if ($this->status) {
			$criteria->compare('status', $this->status);
		}
		if ($this->opened)
		{
			$criteria->compare('opened', $this->opened);
		}
		$criteria->compare('to_address', $this->to_address, true);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
			'pagination' => array(
				'pageSize' => 100 /* It should be better configured at the main.php config file */
			)
		));
	}

}
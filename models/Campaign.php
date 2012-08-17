<?php
/**
 * Campaign class
 *
 * @author antonio ramirez <antonio@clevertech.biz>
 */
/**
 * This is the model class for table "campaign".
 *
 * The followings are the available columns in table 'campaign':
 * @property string $id
 * @property string $name
 * @property string $utm_source
 * @property string $utm_medium
 * @property string $utm_term
 * @property string $utm_content
 * @property string $subject
 * @property string $body_html
 * @property string $body_text
 * @property string $custom
 * @property integer $to_subscribers
 * @property string $selected_users
 * @property integer $template_id
 * @property integer $total_sent
 * @property integer $total_failed
 * @property integer $total_list
 * @property integer $total_opened
 * @property integer $create_time
 * @property integer $scheduled_for
 * @property string $sent_at
 */
class Campaign extends CActiveRecord
{
	const STATUS_DRAFT = 1;
	const STATUS_PROCESSING = 2;
	const STATUS_READY = 3;
	const STATUS_SENT = 4;
	const STATUS_ERROR = 5;
	const STATUS_TEST = 6;

	/*
	replacement tokens
	key = the token to replace
	value = the User model attribute to use
	MODIFY TO SUIT USER table attributes!
	@see CampaignTest::sendUniqueTest if you change below
	*/
	public $tokens = array(
		/* '{{firstname}}' => 'first_name', */
		/* '{{lastname}}' => 'last_name', */
		/* '{{username}}' => 'name', */
		'{{email}}' => 'email'
	);

	/**
	 * Returns the static model of the specified AR class.
	 * @return Campaign the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return array the model behaviors
	 */
	public function behaviors()
	{
		return array(
			'date' => array(
				'class' => 'DateFormatBehavior',
				'dateColumns' => array('scheduled_for'),
				'dateFormat' => 'm/d/Y'
			)
		);
	}

	/**
	 * Saves the time when campaign was created
	 * @return bool
	 */
	public function beforeSave()
	{
		if ($this->isNewRecord)
		{
			$this->create_time = time();
		}
		return parent::beforeSave();
	}

	/**
	 * Forbids deletion when a campaign has emails sent (stats)
	 * @return bool
	 */
	public function beforeDelete()
	{
		$emailAmount = Yii::app()->db
			->createCommand("SELECT COUNT(*) FROM campaign_email WHERE campaign_id=:campaign_id")
			->bindValue(":campaign_id", $this->id)
			->queryScalar();

		return $emailAmount ? false : parent::beforeDelete();
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'campaign';
	}

	/**
	 * @return array the rules of Campaign model
	 */
	public function rules()
	{
		return array(
			array('name, subject, body_html, body_text, utm_source, utm_medium', 'required'),
			array('to_subscribers, total_sent, total_failed, total_list, total_opened, status, template_id, create_time, status',
				'numerical', 'integerOnly' => true),
			array('name, utm_source, utm_medium, utm_term, utm_content', 'length', 'max' => 200),
			array('custom, scheduled_for', 'safe'),
			array('id, name, utm_source, utm_medium, subject, body_html, body_text, custom,
				total_sent, total_failed, total_list, total_opened, sent_at, status, template_id, to_subscribers',
				'safe', 'on' => 'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => Yii::t('ses', 'Campaign Name'),
			'utm_source' => 'utm-source',
			'utm_medium' => 'utm-medium',
			'utm_term' => 'utm-term',
			'utm_content' => 'utm-content',
			'to_subscribers' => Yii::t('ses', 'To Subscribers'),
			'subject' => Yii::t('ses', 'Subject'),
			'body_html' => Yii::t('ses', 'Body HTML'),
			'body_text' => Yii::t('ses', 'Body Text'),
			'total_sent' => Yii::t('ses', 'Total Sent'),
			'total_failed' => Yii::t('ses', 'Total Failed'),
			'total_opened' => Yii::t('ses', 'Total Opened'),
			'total_list' => Yii::t('ses', 'Total List'),
			'status' => Yii::t('ses', 'Status'),
			'template_id' => Yii::t('ses', 'Template'),
			'custom' => Yii::t('ses', 'Custom Recipients'),
			'sent_at' => Yii::t('ses', 'Date Sent'),
			'create_time' => Yii::t('ses', 'Date Created'),
			'scheduled_for' => Yii::t('ses', 'Scheduled For')
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria = new CDbCriteria;

//		$criteria->compare('id', $this->id);
		$criteria->compare('name', $this->name, true);
//		$criteria->compare('utm_source', $this->utm_source, true);
//		$criteria->compare('utm_medium', $this->utm_medium, true);
//		$criteria->compare('utm_term', $this->utm_term, true);
//		$criteria->compare('utm_content', $this->utm_content, true);
		$criteria->compare('subject', $this->subject, true);
//		$criteria->compare('body_html', $this->body_html, true);
//		$criteria->compare('body_text', $this->body_text, true);
//		$criteria->compare('total_sent', $this->total_sent);
//		$criteria->compare('total_failed', $this->total_failed);
//		$criteria->compare('total_list', $this->total_list);
		if($this->status)
		{
			$criteria->compare('status', $this->status);
		}
//		$criteria->compare('to_subscribers', $this->to_subscribers);
//		$criteria->compare('sent_at', $this->sent_at);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}

	/**
	 * Property to return labelled status, mainly used on the grid
	 * @return string
	 */
	public function getLabelledStatus()
	{

		$html = '<span class="badge badge-{rep}">{status}</span>';

		$rep = array(
			self::STATUS_READY => array('important', 'Ready'),
			self::STATUS_DRAFT => array('info', 'Draft'),
			self::STATUS_SENT => array('success', 'Sent'),
			self::STATUS_PROCESSING => array('warning', 'Processing'),
			self::STATUS_ERROR => array('error', 'Error'),
			self::STATUS_TEST => array('default', 'Test')
		);

		if (!$this->status)
		{
			$this->status = 1;
		}
		return strtr($html, array('{rep}' => @$rep[$this->status][0], '{status}' => @$rep[$this->status][1]));
	}

	/**
	 * Property to return simple status array
	 * @return string
	 */
	public function getStatusArray()
	{
		return array(
			self::STATUS_DRAFT => Yii::t('ses', 'Draft'),
			self::STATUS_READY => Yii::t('ses', 'Ready to send'),
			self::STATUS_SENT => Yii::t('ses', 'Sent'),
			self::STATUS_PROCESSING => Yii::t('ses', 'Processing'),
			self::STATUS_ERROR => Yii::t('ses', 'Error'),
			self::STATUS_TEST => Yii::t('ses', 'Test')
		);
	}

	/**
	 * Property to return named status instead of its integer value
	 * @return string
	 */
	public function getNamedStatus()
	{
		return strip_tags($this->getLabelledStatus());
	}

	/**
	 * Property to return the total number of emails opened of a campaign
	 * @return mixed
	 */
	public function getTotalOpened()
	{
		return $this->total_opened? $this->total_opened : Yii::app()->db
			->createCommand('SELECT COUNT(*) FROM campaign_email WHERE campaign_id=:campaign_id AND opened=1')
			->bindValue(':campaign_id', $this->id)
			->queryScalar();
	}

	/**
	 * Calculates the number of users a campaign would be sent to
	 * @param $campaign
	 * @return mixed
	 */
	public function getTotalUsers($campaign)
	{
		$notIn = array();
		$usersCount = $invitesCount = 0;
		$userCriteria = null;

		if(@$campaign['to_subscribers'])
		{
			$userCriteria = new CDbCriteria();
			$userCriteria->compare('subscribed', 1);
			$userCriteria->addCondition('t.email NOT IN (SELECT campaign_unsubscribed.email FROM campaign_unsubscribed WHERE campaign_unsubscribed.email=t.email)');
		}

		if (@$campaign['selected_users'])
		{
			$emails = explode(',', $campaign['selected_users']);
			foreach ($emails as $email)
			{
				$email = trim($email);

				if (strlen($email))
				{
					$notIn[] = $email;
				}
			}
		}

		if (@$campaign['custom'])
		{
			$emails = explode(',', $campaign['custom']);

			foreach ($emails as $email)
			{
				$email = trim($email);

				if (strlen($email) && !in_array($email, $notIn))
				{
					$notIn[] = $email;
				}
			}
		}
		$notInCount = count($notIn);
		if ($userCriteria)
		{
			$userCriteria->addNotInCondition('email', $notIn);
			$usersCount = User::model()->count($userCriteria);
		}

		return $notInCount + $usersCount ;
	}

	/**
	 * Helper function
	 * @return bool
	 */
	public function validateStatusReady()
	{
		return $this->status == Campaign::STATUS_READY && !$this->custom && !$this->selected_users && !$this->to_subscribers;
	}

	/**
	 * returns the google analytics params
	 * @return string
	 */
	public function getGAParams()
	{
		return http_build_query(array(
			'utm_campaign' => $this->name,
			'utm_term' => $this->utm_term,
			'utm_medium' => $this->utm_medium,
			'utm_source' => $this->utm_source,
			'utm_content' => $this->utm_content
		));
	}

	/**
	 * function required to create the emailonacid Required Nonce (number used once) value.
	 * This should be unique for every request and MUST NOT contain a forward slash (/) or exceed 255 characters.
	 * @static
	 * @param int $size
	 * @return mixed
	 */
	public static function nonce($size = 32)
	{ //256 bit == 32byte.
		$ret = "";
		for ($x = 0; $x < $size; $x++)
		{
			while (true)
			{
				$char = chr(mt_rand(0, 255));
				if ($char != "/") break;
			}
			$ret .= $char;
		}
		return str_replace("/", "", base64_encode($ret));
	}
}

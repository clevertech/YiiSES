<?php

/**
 * This is the model class for table "campaign_unsubscribed".
 *
 * The followings are the available columns in table 'campaign_unsubscribed':
 * @property integer $id
 * @property string $email
 * @property integer $create_time
 */
class Unsubscribed extends CActiveRecord
{

	const TOKEN_NAME = 'utoken';

	const ACTION_UNSUBSCRIBE = 'unsubscribe-submit';

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
		return 'campaign_unsubscribed';
	}

	/**
	 * Saves its creation time to database
	 * @return bool
	 */
	public function beforeSave()
	{
		if (parent::beforeSave())
		{
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
			array('email', 'required'),
			array('email', 'email'),
			array('email', 'length', 'max' => 254),
			array('email', 'unique', 'message' => Yii::t('ses', 'Email "{value}" has already been removed from our lists.')),
			array('id, email, create_time', 'safe', 'on' => 'search')
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('email', $this->email, true);
		$criteria->compare('create_time', $this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
			'pagination' => array(
				'pageSize' => 100
			)
		));
	}

	/**
	 * Adds a guest to the to unsubscribed email list. Emails on that list, do not receive *any* campaigns
	 * @param $attributes
	 * @return array|null
	 */
	public function addGuest($attributes)
	{
		$data = null;
		$user = User::model()->findByAttributes($attributes);

		if ($user)
		{
			// make sure the control panel has the correct url
			$link = CHtml::link(Yii::t('ses', 'use the control panel'), array(Yii::app()->params['ses.panel.login.url']));
			$data = array(
				'status' => 501,
				'message' => Yii::t('ses', 'This email belongs to a registered user. Please, {LINK} to unsubscribe.', array('{LINK}' => $link)),
				'type' => 'error'
			);
		} else
		{
			$this->setAttributes($_POST['Unsubscribed']);
			if ($this->save())
			{
				$data = array(
					'status' => 200,
					'message' => Yii::t('ses', 'Email successfully removed from our list'),
					'type' => 'alert-success'
				);
			} else
			{
				$data = array(
					'status' => 501,
					'message' => $this->getError('email'),
					'type' => 'alert-error'
				);
			}
		}

		return $data;
	}

	/**
	 * Creates an unsubscribe encoded token to include in the templates that will be inserted as a paramter
	 * into the 'unsubscribe' link at the bottom of the emails sent. This token will obscure the id of the user.
	 * @static
	 * @param $userId the user id to obscure
	 * @return string
	 */
	public static function getUnsubscribeToken($userId)
	{
		return urlencode(base64_encode(uniqid() . '#' . $userId));
	}

	/**
	 * Decodes the token and returns the obscured user id, false if separator is not found
	 * @static
	 * @param $token the token to decode
	 * @return bool|string
	 */
	public static function getUserIdFromToken($token)
	{
		$decoded = base64_decode(urldecode($token));
		$pos = strpos($decoded, '#') + 1;
		return $pos === false ? false : substr($decoded, $pos);
	}

	public static function alert($message, $type = '')
	{
		return CHtml::tag('div', array('class' => 'alert-message ' . $type), $message);
	}

	/**
	 * Updates member to
	 * @static
	 */
	public static function addMember()
	{
		$token = Yii::app()->user->getState(self::TOKEN_NAME);
		if ($token)
		{
			$id = self::getUserIdFromToken($token);
			$user = User::model()->findByPk($id);
			if ($user)
			{
				// the subscription field
				if ($user->hasAttribute('subscribed'))
				{
					$user->subscribed = 0;
				}
				if ($user->save())
				{
					Yii::app()->user->setFlash(
						self::ACTION_UNSUBSCRIBE . '-message',
						self::alert(Yii::t('ses', 'Successfully unsubscribed from receiving any more updates (check your profile).'), 'success'));
				} else

					Yii::app()->user->setFlash(self::ACTION_UNSUBSCRIBE . '-message', self::alert(CHtml::errorSummary($user), 'error'));

			} else
				Yii::app()->user->setFlash(
					self::ACTION_UNSUBSCRIBE . '-message',
					self::alert(Yii::t('ses', 'User not found. Unsubscription aborted.'), 'error'));
		} else
			self::abortAction();
	}

	/**
	 * Helper function to return a common error message for unauthorized requests
	 * @static
	 * @param string $message
	 */
	private static function abortAction($message = "Unauthorized request")
	{
		Yii::app()->user->setFlash(self::ACTION_UNSUBSCRIBE . '-message', self::alert($message . Yii::t('ses', ' Unsubscription aborted.'), 'error'));
	}
}
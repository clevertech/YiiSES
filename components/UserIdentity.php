<?php
/**
 * UserIdentity.php
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * Date: 7/29/12
 * Time: 2:50 PM
 */
class UserIdentity extends CUserIdentity
{
	/**
	 * Authenticates a user.
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
		$password = Yii::app()->getModule('ses')->password;
		if ($password === null)
			throw new CException(Yii::t('ses', 'Please configure the "password" property of the "ses" module.'));
		else if ($password === false || $password === $this->password)
			$this->errorCode = self::ERROR_NONE;
		else
			$this->errorCode = self::ERROR_UNKNOWN_IDENTITY;
		return !$this->errorCode;
	}
}
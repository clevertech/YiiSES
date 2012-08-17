<?php
/**
 * UnsubscribedController class
 *
 * Handles unsubscriptions
 *
 * User: antonio ramirez <antonio@clevertech.biz>
 * Date: 7/12/12
 * Time: 6:25 PM
 */
class UnsubscribedController extends CController
{
	public $defaultAction = 'manage';

	/**
	 * Renders list of campaigns
	 */
	public function actionManage()
	{

		$unsubscribed = new Unsubscribed();

		if (isset($_GET['Unsubscribed']))
		{
			$unsubscribed->attributes = $_GET['Unsubscribed'];
		}

		$this->render('manage', array('unsubscribed' => $unsubscribed));
	}

	/**
	 * Adds new email to black list
	 */
	public function actionCreate()
	{

		if (isset($_POST['Unsubscribed']))
		{
			$model = new Unsubscribed();
			$model->attributes = $_POST['Unsubscribed'];
			if ($model->save())
			{
				Yii::app()->user->setFlash('success', Yii::t('ses', 'Email "{EMAIL}" successfully added!', array('{EMAIL}' => $model->email)));
			} else
			{
				Yii::app()->user->setFlash('error', Yii::t('ses', 'An unexpected error occurred. Unable to proceed.'));
			}
		}
		$this->redirect(array('unsubscribed/manage'));
	}

	/**
	 * Removes an email from the blocked list
	 * @param $id
	 */
	public function actionDelete($id)
	{
		if (Yii::app()->request->isAjaxRequest)
		{
			$model = Unsubscribed::model()->findByPk((int)$id);
			if ($model && $model->delete())
			{
				echo AlertHelper::formatMessage(Yii::t('ses', 'Email "{EMAIL}" successfully removed.', array('{EMAIL}' => $model->email)), AlertHelper::SUCCESS);
			} else
			{
				echo AlertHelper::formatMessage(Yii::t('ses', 'An unexpected error has occurred. Unable to remove email.'), AlertHelper::ERROR);
			}
		}
	}

	/**
	 * Public action to display the form to allow users to unsubscribe
	 * @see EmailModule.php beforeControllerAction
	 */
	public function actionForm()
	{
		$this->layout = 'unsubscribe';

		$model = new Unsubscribed();

		$r = Yii::app()->getRequest();

		if ($r->isAjaxRequest && isset($_POST['Unsubscribed']))
		{
			$data = $model->addGuest($_POST['Unsubscribed']);
			echo CJSON::encode($data);
			Yii::app()->end();
		}

		if ($r->getParam(Unsubscribed::ACTION_UNSUBSCRIBE) !== null)
		{
			Unsubscribed::addMember();
		}

		$token = $r->getParam('token', Yii::app()->user->getState(Unsubscribed::TOKEN_NAME));

		if ($token)
		{
			if (!Yii::app()->user->getState(Unsubscribed::TOKEN_NAME))
			{
				Yii::app()->user->setState(Unsubscribed::TOKEN_NAME, $token);
			}
		}

		$this->render('form', array('model' => $model));
	}
}

<?php
/**
 * CampaignController class
 *
 * Handles requests to edit campaigns
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 */
class CampaignController extends CController
{
	public $defaultAction = 'manage';

	/**
	 * Renders list of campaigns
	 */
	public function actionManage()
	{

		$campaign = new Campaign;

		if (isset($_GET['Campaign']))
		{
			$campaign->attributes = $_GET['Campaign'];
		}

		$this->render('manage', array('campaign' => $campaign));
	}

	/**
	 * Creates a campaign
	 */
	public function actionCreate()
	{
		$model = new Campaign();

		$this->checkGridViewUpdate($model);

		if (isset($_POST['Campaign']))
		{
			$model->attributes = $_POST['Campaign'];
			if ($model->save())
			{
				Yii::app()->user->setFlash('success', 'Campaign successfully created!');
				$this->redirect(array('campaign/update', 'id' => $model->id));
			} else
			{
				Yii::app()->user->setFlash('error', 'An unexpected error occurred. Unable to create campaign.');
			}

		}
		$this->render('edit', array('model' => $model));
	}

	/**
	 * Updates a campaign
	 * @param $id
	 * @throws CHttpException
	 */
	public function actionUpdate($id)
	{
		$model = Campaign::model()->findByPk((int)$id);
		if ($model)
		{

			$this->checkGridViewUpdate($model);

			if (isset($_POST['Campaign']))
			{
				$model->attributes = $_POST['Campaign'];
				if ($model->validateStatusReady())
				{
					Yii::app()->user->setFlash('error', 'You must select at a recipient!<br/>The campaign cannot change its status this way.');

					$model->refresh();
				} else if ($model->save())
				{
					Yii::app()->user->setFlash('success', 'Campaign successfully updated!');
				}

			}

			$this->render('edit', array('model' => $model));

		} else
			throw new CHttpException(404);
	}

	/**
	 * Displays campaign statistics
	 * @param $id
	 * @throws CHttpException
	 */
	public function actionStats($id)
	{
		$model = Campaign::model()->findByPk((int)$id);

		if ($model)
		{
			if (!$model->total_list)
				$this->redirect(array('campaign/update', 'id' => $id));

			$email = new Email();
			$email->status = null;
			$email->campaign_id = $model->id;

			if (isset($_GET['Email']))
			{
				$email->setAttributes($_GET['Email'], false);
			}
			$this->render('stats', array('model' => $model, 'email' => $email));

		} else
			throw new CHttpException(404);
	}

	/**
	 * Deletes the campaign
	 * @param $id
	 */
	public function actionDelete($id)
	{
		if (Yii::app()->request->isAjaxRequest)
		{
			$model = Campaign::model()->findByPk((int)$id);
			if ($model && $model->delete())
			{
				echo AlertHelper::formatMessage(Yii::t('ses', 'Campaign Successfully deleted.'), AlertHelper::SUCCESS);
			} else
			{
				echo  AlertHelper::formatMessage(Yii::t('ses', 'Unable to delete campaign (probably due to statistical data).'), AlertHelper::ERROR);
			}
		}
	}

	/**
	 * Returns the total number of users that are going to receive the campaign
	 * @throws CHttpException
	 */
	public function actionUsers()
	{
		$params = Yii::app()->getRequest()->getParam('Campaign');
		if ($params !== null)
		{
			$count = Campaign::model()->getTotalUsers($params);

			echo Yii::t('ses', '<strong>{COUNT}</strong> users will receive this campaign.', array('{COUNT}' => $count));
			echo Yii::t('ses', '<br/><br/><p><strong>NOTE:</strong> duplicated and unsubscribed emails will be skipped</p>');

		} else
			throw new CHttpException(404);
	}

	/**
	 * Preview functionality
	 * @param $id
	 * @throws CHttpException
	 */
	public function actionPreview($id)
	{
		$model = Campaign::model()->findByPk((int)$id);

		if ($model)
		{
			echo Email::formatHTMLMessage($model->body_html, Template::getView(Yii::app()->getRequest()->getParam('template', $model->template_id)));
		} else
			throw new CHttpException(404);
	}

	/**
	 * Body parse functionality
	 * @param $id
	 * @throws CHttpException
	 */
	public function actionTemplate($id)
	{
		$model = Campaign::model()->findByPk((int)$id);

		$template_id = Yii::app()->getRequest()->getParam('template', ($model ? $model->template_id : null));

		if ($template_id !== null)
			echo Email::formatHTMLMessage(Yii::t('ses', '<p>Your new content here</p>'), Template::getView($template_id, Template::BODY_VIEW));
		else
			throw new CHttpException(404);
	}

	/**
	 * This action is for testing purposes and only when environment is not on production.
	 * It can be removed when moved to production branch.
	 * @param $id
	 * @throws CHttpException
	 */
	public function actionTest($id)
	{
		$model = Campaign::model()->findByPk((int)$id);

		if ($model && Yii::app()->getRequest()->isAjaxRequest)
		{

			$tester = new CampaignTest();
			$messages = '<h3>Test finished</h3>';
			try
			{
				if ($tester->sendUniqueTest($model) === true)
				{
					$messages .= Yii::t('ses', 'Email test send. Please, check the email box.<br/>');
				}
			} catch (Exception $e)
			{
				$messages .= Yii::t('ses', 'An error has occurred: {ERROR}<br/>Test failed.', array('{ERROR}' => $e->getMessage()));
			}
			echo $messages;

		} else
			throw new CHttpException(404);
	}

	/**
	 * Test campaigns using the command
	 * * USE WITH CARE * as the command will be running with whatever options you set. So if you create test a campaign
	 * that is set to send to all subscribers, it will be sent to all subscribers!!
	 * @param string $secret_key
	 * @see CampaignTest::SECRET_KEY
	 */
	public function actionTestCommand($secret_key = '')
	{
		if ($secret_key == CampaignTest::SECRET_KEY)
		{
			$action = Yii::app()->getRequest()->getParam('action');
			$this->render('command', array('action' => $action));
		} else echo Yii::t('ses', 'Incorrect secret key.');
	}

	/**
	 * Handles redactorJS image uploads
	 */
	public function actionUpload()
	{
		$dir = @Yii::app()->params['ses.files.path'];
		$url = @Yii::app()->params['ses.files.url'];
		$file = CUploadedFile::getInstanceByName('file');
		$json = array();
		if ($dir && is_dir($dir) && is_writable($dir) && $url && $file)
		{
			if (in_array(strtolower($file->type), array('image/png', 'image/jpg', 'image/jpeg', 'image/pjpeg')))
			{
				$filename = mt_rand() . '-' . strtolower($file->name);
				if ($file->saveAs($dir . DIRECTORY_SEPARATOR . $filename))
				{
					$json['filelink'] = Yii::app()->params['ses.files.url'] . $filename;
				}
			}
		}
		echo stripslashes((function_exists('json_encode') ? json_encode($json) : CJSON::encode($json)));
	}

	/**
	 * Handles redactorJS gallery display
	 */
	public function actionGallery()
	{
		$json = array();
		$dir = @Yii::app()->params['ses.files.path'];
		$url = @Yii::app()->params['ses.files.url'];
		if ($dir && is_dir($dir) && $url)
		{

			$url = substr($url, -1, 1) == '/' ? $url : $url . '/';
			$files = CFileHelper::findFiles($dir, array('fileTypes' => array('jpeg', 'gif', 'jpg', 'png')));
			foreach ($files as $file)
			{
				$file = explode(DIRECTORY_SEPARATOR, $file);
				$filename = array_pop($file);
				$json[] = array('thumb' => $this->createUrl('campaign/thumb', array('file' => $filename)), 'image' => $url . $filename);
			}

		}
		echo stripslashes((function_exists('json_encode') ? json_encode($json) : CJSON::encode($json)));
	}

	/**
	 * Handles Thumbnail requests from redactorJS
	 * @param $file
	 */
	public function actionThumb($file)
	{
		$dir = @Yii::app()->params['ses.files.path'];
		if ($dir && is_dir($dir) && is_file($dir . DIRECTORY_SEPARATOR . $file))
		{
			Yii::import('ses.extensions.EPhpThumb.EPhpThumb', true);

			$thumb = new EPhpThumb();
			$thumb->init();
			$thumb->create($dir . DIRECTORY_SEPARATOR . $file)
				->resize(100, 100)
				->show(true);
		}
	}

	/**
	 * Checkes whether the request is a CGridView ajax one. If so, render only the view withouth processing its scripts
	 * again.
	 * @param $model the campaign ActiveRecord
	 */
	protected function checkGridViewUpdate($model)
	{
		$ajax = Yii::app()->getRequest()->getParam('ajax');
		if ($ajax == 'campaign-users-grid')
		{
			$user = new User();

			if (Yii::app()->getRequest()->getParam('User'))
			{
				$user->setAttributes(Yii::app()->getRequest()->getParam('User'), false);
			}
			$criteria = new CDbCriteria;
			$criteria->compare('name', $user->name, true);
			$criteria->compare('email', $user->email, true);
			$criteria->compare('confirmed', '1');
			$criteria->addCondition('email NOT IN (SELECT Email FROM campaign_unsubscribed)');
			$dataProvider = new CActiveDataProvider('User', array('criteria' => $criteria, 'pagination' => array('pageSize' => 25)));

			$this->renderPartial('_users', array('selected_users' => $model->selected_users, 'user' => $user, 'dataProvider' => $dataProvider));

			Yii::app()->end();
		}
	}
}


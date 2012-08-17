<?php
/**
 * AmazonController class
 *
 * Renders amazon SES console panel
 */
class AmazonController extends CController
{
	public $defaultAction = 'manage';

	/**
	 * Renders Amazon SES dashboard
	 */
	public function actionManage()
	{
		$this->render('manage');
	}

}

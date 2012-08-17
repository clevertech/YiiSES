<?php
/**
 * MessageController class
 *
 * Handles the public action that renders a blank 1x1 pixels gif image and updates campaign stats.
 *
 * @author antonio ramirez <antonio@ramirezcobos.com>
 *
 * Date: 7/3/12
 * Time: 4:18 PM
 */
class MessageController extends CController
{

	/**
	 * Updates email and campaign opened counters and displays blank.gif image
	 * @param $id
	 */
	public function actionImage($id)
	{
		$model = Email::model()->findByPk((int)$id);
		if($model)
		{
			$model->opened = 1;
			if($model->save())
			{
				// update campaign now
				Yii::app()->db
					->commandBuilder
					->createUpdateCounterCommand('campaign', array(
					'total_opened' => 1,
				), new CDbCriteria(array(
					'condition' => 'id=:id',
					'params' => array(
						':id' => $model->campaign_id
					))))
					->execute();
			}
		}
		header('Content-Type: image/gif');
		echo base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
	}

}

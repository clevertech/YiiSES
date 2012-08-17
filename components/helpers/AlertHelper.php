<?php
/**
 * AlertHelper class
 *
 * Helper class to format bootstrap alert info boxes
 *
 * User: antonio ramirez <antonio@clevertech.biz>
 * Date: 7/11/12
 * Time: 5:05 PM
 *
 */
class AlertHelper
{
	const SUCCESS = 'alert-success';
	const ERROR = 'alert-error';
	const INFO = 'alert-information';
	const WARNING = '';

	private static $_closeButton = '<a class="close" data-dismiss="alert" href="#">×</a>';
	private static $_alert = '<div class="alert {TYPE}">{CLOSE}{MESSAGE}</div>';

	public static function formatMessage( $message, $type=self::WARNING, $closeButton=true)
	{
		$type = $type==self::SUCCESS || $type==self::ERROR || $type==self::INFO || $type==self::WARNING? $type : self::WARNING;

		$closeButton = $closeButton ? self::$_closeButton : '';

		$data = array('{TYPE}'=>$type, '{CLOSE}'=>$closeButton, '{MESSAGE}'=>$message);

		return strtr(self::$_alert, $data);

	}
}

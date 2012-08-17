<?php
/**
 * Template class
 * User: antonio
 * Date: 6/22/12
 * Time: 11:21 PM
 *
 * Email templates Model helper
 */
class Template
{
	const BLANK = 0;
	const SIMPLE = 1;

	const MAIN_VIEW = 'mainView';
	const BODY_VIEW = 'bodyView';

	/**
	 *
	 * @var array $templates holds the attributes to parse body_html
	 * You can create your own templates following the same patter.
	 * @see ses.views.template
	 *
	 */
	protected static $templates = array(
		self::BLANK => array(
			'text' => 'blank',
			'description' => 'Plain template, with a logo ',
			'imageSrc' => '/images/templates/blank.jpg',
			'mainView' => 'blank.php',
			'bodyView' => '_blank_body.php'
		),
		self::SIMPLE => array(
			'text' => 'Simple',
			'description' => 'Simple template, with social links at the header and an address on footer',
			'imageSrc' => '/images/templates/simple.jpg',
			'mainView' => 'simple.php',
			'bodyView' => '_simple_body.php'
		)
	);

	/**
	 * Returns template name
	 * @static
	 * @param int $template
	 * @return bool
	 */
	public static function getTemplateName($template = self::BLANK)
	{
		return self::_getValue($template, 'text');
	}

	/**
	 * Returns the view file path
	 * @static
	 * @param int $template
	 * @param string $viewType could be 'mainView' (default) or 'bodyView'
	 * @return bool|string
	 */
	public static function getView($template = self::BLANK, $viewType=self::MAIN_VIEW)
	{
		$viewType = $viewType == self::MAIN_VIEW || $viewType == self::BODY_VIEW ? $viewType : self::MAIN_VIEW;

		// make sure the root alias and the module is correctly set  (this is the setup when working with YiiBoilerplate)
		$path = Yii::getPathOfAlias('root.backend.modules.ses.views.template') . DIRECTORY_SEPARATOR;
		// setup for default Yii setup
		// $path = Yii::getPathOfAlias('application.modules.ses.views.template') . DIRECTORY_SEPARATOR;

		$view = self::_getValue($template, $viewType);

		return $view ? $path . $view : false;
	}

	/**
	 * Returns javascript data options for ddslick
	 * @static
	 * @param $assetsUrl
	 * @param int $selected
	 * @return string
	 */
	public static function getJSData($assetsUrl, $selected = self::BLANK)
	{
		$data = array();
		foreach (self::$templates as $key => $template) {
			$data[] = CMap::mergeArray(
				array_slice($template, 0, 4, true),
				array('imageSrc' => $assetsUrl . $template['imageSrc'], 'value' => $key, 'selected' => ($selected == $key))
			);
		}
		return CJSON::encode($data);
	}

	/**
	 * Returns a specific value in the $templates array specifying the template key
	 * @static
	 * @param $key
	 * @param $value
	 * @return bool
	 */
	protected static function _getValue($key, $value)
	{
		return array_key_exists($key, self::$templates) ? self::$templates[$key][$value] : false;
	}


}

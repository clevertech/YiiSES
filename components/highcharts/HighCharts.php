<?php

class HighCharts extends CWidget
{

	public $options = array();
	public $htmlOptions = array();

	/**
	 * Renders the widget.
	 */
	public function run()
	{

		if (isset($this->htmlOptions['id']))
			$this->setId($this->htmlOptions['id']);
		else
			$this->htmlOptions['id'] = $this->getId();

		echo CHtml::openTag('div', $this->htmlOptions);
		echo CHtml::closeTag('div');

		if (is_string($this->options)) {
			$this->options = function_exists('json_decode') ? json_decode($this->options) : CJSON::decode($this->options);
			if (!$this->options)
				throw new CException(Yii::t('HighCharts', 'The options parameter is not valid JSON.'));
		}
		$defaultOptions = array('chart' => array('renderTo' => $this->getId(), 'defaultSeriesType'=>'line','borderRadius'=>10, 'borderWidth'=>1), 'exporting' => array('enabled' => true));
		$this->options = CMap::mergeArray($defaultOptions, $this->options);
		$jsOptions = CJavaScript::encode($this->options);
		$this->registerScripts(__CLASS__ . '#' . $this->getId(), "var chart_" . $this->getId() . " = new Highcharts.Chart($jsOptions);", CClientScript::POS_READY);
	}

	/**
	 * Publishes and registers the necessary script files.
	 *
	 * @param string the id of the script to be inserted into the page
	 * @param string the embedded script to be inserted into the page
	 */
	protected function registerScripts($id, $embeddedScript)
	{
		$basePath = dirname(__FILE__) . DIRECTORY_SEPARATOR .
			'assets' . DIRECTORY_SEPARATOR;
		$baseUrl = Yii::app()->getAssetManager()->publish($basePath, false, 1);
		$scriptFile = YII_DEBUG ? '/highcharts.src.js' : '/highcharts.js';

		$cs = Yii::app()->clientScript;
		$cs->registerCoreScript('jquery');
		$cs->registerScriptFile($baseUrl . $scriptFile, CClientScript::POS_END);

		if ($this->options['exporting']['enabled']) {
			$scriptFile = YII_DEBUG ? 'exporting.src.js' : 'exporting.js';
			$cs->registerScriptFile("$baseUrl/modules/$scriptFile", CClientScript::POS_END);
		}
		$cs->registerScript($id, $embeddedScript);
	}
}


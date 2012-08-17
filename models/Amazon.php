<?php
/**
 * Amazon class
 *
 * Handles different requests to amazon ses services
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 */
Yii::import('ses.components.ses.SES', true);

class Amazon
{
	const DAY_INTERVAL = 'Y-m-d';
	const HOUR_INTERVAL = 'Y-m-d H:00:00';
	/**
	 * @var $_m Amazon reference
	 */
	private static $_m;

	/**
	 * @var SES component
	 * @see email/components/ses/SES.php
	 */
	protected $_ses;

	protected $_quota = null;

	protected $_usage = null;

	/**
	 * constructor
	 *
	 * Initiates ses component with configured API key credentials
	 */
	public function __construct()
	{
		$this->_ses = new SES(Yii::app()->params['ses.aws.key'], Yii::app()->params['ses.aws.secret']);
	}

	/**
	 * @static singleton
	 * @return Amazon
	 */
	public static function model()
	{
		if (isset(self::$_m))
			return self::$_m;

		self::$_m = new Amazon();
		return self::$_m;
	}

	/**
	 * Gets send quota SES API response
	 * @return array|bool
	 */
	public function getSendQuota()
	{
		return $this->_ses->getSendQuota();
	}

	/**
	 * Gets send statistics SES API response
	 * @return array|bool
	 * @since  2012-06-12 sort results by timestamp ASC antonio ramirez <antonio@clevertech.biz>
	 */
	public function getSendStatistics()
	{

		if ($this->_usage === null)
		{
			$this->_usage = $this->_ses->getSendStatistics();

			$timestamp = array();

			foreach ($this->_usage['member'] as $key => $member)
			{
				$timestamp[$key] = $member['Timestamp'];
			}

			array_multisort($timestamp, SORT_ASC, $this->_usage['member']);
		}
		return $this->_usage;
	}

	/**
	 * Helper function to get SentLast24Hours quota data
	 * @return mixed
	 */
	public function getSentLast24Hours()
	{
		return $this->_getQuotaData('SentLast24Hours');
	}

	/**
	 * Helper function to get Max24HourSend quota data
	 * @return mixed
	 */
	public function getMax24HourSend()
	{
		return $this->_getQuotaData('Max24HourSend');
	}

	/**
	 * Helper function to get MaxSendRate quota data
	 * @return mixed
	 */
	public function getMaxSendRate()
	{
		return $this->_getQuotaData('MaxSendRate');
	}

	/**
	 * Prepares Amazon SES response for Highcharts datetime type series display
	 *
	 * @param string $interval
	 * @return array the resulting dataset
	 */
	public function getStatsData($interval = self::DAY_INTERVAL)
	{

		$interval = $interval == self::DAY_INTERVAL || $interval == self::HOUR_INTERVAL ? $interval : self::DAY_INTERVAL;
		$dataSet = $data = array();

		$stats = $this->getSendStatistics();

		foreach ($stats['member'] as $member)
		{
			$time = strtotime(str_replace(array('T', 'Z'), array(' ', ' '), $member['Timestamp'])); // * 1000;

			$hour = date($interval, $time);
			if (array_key_exists($hour, $data))
			{

				$data[$hour]['attempts'] += intval($member['DeliveryAttempts']);
				$data[$hour]['bounces'] += intval($member['Bounces']);
				$data[$hour]['rejects'] += intval($member['Rejects']);
				$data[$hour]['complaints'] += intval($member['Complaints']);
			} else
			{
				$data[$hour] = array(
					'attempts' => intval($member['DeliveryAttempts']),
					'bounces' => intval($member['Bounces']),
					'rejects' => intval($member['Rejects']),
					'complaints' => intval($member['Complaints'])
				);
			}
		}
		$dataSet['attempts'] = $dataSet['bounces'] = $dataSet['rejects'] = $dataSet['complaints'] = array();

		foreach ($data as $hour => $values)
		{
			$dataSet['attempts'][] = array(strtotime($hour) * 1000, $values['attempts']);
			$dataSet['bounces'][] = array(strtotime($hour) * 1000, $values['bounces']);
			$dataSet['rejects'][] = array(strtotime($hour) * 1000, $values['rejects']);
			$dataSet['complaints'][] = array(strtotime($hour) * 1000, $values['complaints']);
		}

		return $dataSet;
	}

	/**
	 * Helper function to get quota stats
	 * @param $key
	 * @return mixed
	 */
	private function _getQuotaData($key)
	{
		if ($this->_quota === null)
		{
			$this->_quota = $this->getSendQuota();
		}
		return @$this->_quota[$key];
	}
}

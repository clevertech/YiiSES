<?php
/**
 * StringToArrayBehavior.php
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * Date: 7/31/12
 * Time: 10:12 PM
 */
class StringToArrayBehavior extends CActiveRecordBehavior
{
	//.. array of columns that have dates to be converted
	public $columns = array();

	public $separator = ',';
	/**
	 * Convert from array to string
	 */
	public function beforeSave($event)
	{
		$this->format($this->columns);
		return parent::beforeSave($event);
	}

	/**
	 * Conver to array again
	 * @param CModelEvent $event
	 */
	public function afterSave($event)
	{
		$this->format($this->columns, false);
		return parent::afterSave($event);
	}
	/**
	 * Converts string to array
	 */
	public function afterFind($event)
	{
		$this->format($this->columns, false);
		return parent::afterFind($event);
	}

	/**
	 * Formats to arrays to string and viceversa
	 * @param mixed $columns the columns attributes to format
	 * @param bool $toString if true will convert to string, array otherwise
	 */
	protected function format($columns, $toString = true)
	{
		if(empty($columns)) return;

		foreach($this->getOwner()->getAttributes() as $key=>$value)
		{
			if(in_array($key, $columns) && !empty($value))
			{

				$dt = $this->getOwner()->{$key};
				if($toString && !is_array($dt)) $dt = array($dt);

				$this->getOwner()->{$key} = $toString ? implode($this->separator, $dt) : explode($this->separator, $dt);
			}
		}
	}
}

<?php
/**
 * will be the base class for all DataWarehouse events to be serialized
 * 
 * @package Scheduler
 * @subpackage DWH
 * @abstract
 */
abstract class VDwhEventBase
{
	const EVENT_FIELD_SEPARATOR = ",";
	const EVENT_LINE_DELIMITER = "\n";
	/**
	 * @return string
	 *
	 */
	public abstract function toEventLine();
	
	/**
	 *
	 * $return  VDwhEventBase
	 */
	public static function fromEventLine ( $event_line )
	{
		 
	}
}
?>
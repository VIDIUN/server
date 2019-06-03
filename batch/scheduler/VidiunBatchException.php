<?php
/**
 * @package Scheduler
 */
class VidiunBatchException extends VidiunException 
{
	public function __construct($message, $code, $arguments = null)
	{
		parent::__construct($message, $code, $arguments);
	}
}
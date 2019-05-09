<?php
/**
 * @package Scheduler
 * @subpackage Conversion.engines
 */
class VConversionEngineMencoder  extends VJobConversionEngine
{
	const MENCODER = "mencoder";
		
	public function getName()
	{
		return self::MENCODER;
	}
	
	public function getType()
	{
		return VidiunConversionEngineType::MENCODER;
	}
	
	public function getCmd ()
	{
		return VBatchBase::$taskConfig->params->mencderCmd;
	}
}

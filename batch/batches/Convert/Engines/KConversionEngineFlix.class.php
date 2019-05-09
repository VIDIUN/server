<?php
/**
 * @package Scheduler
 * @subpackage Conversion.engines
 */
class VConversionEngineFlix  extends VJobConversionEngine
{
	const FLIX = "cli_encode";
	
	public function getName()
	{
		return self::FLIX;
	}
	
	public function getType()
	{
		return VidiunConversionEngineType::ON2;
	}

	public function getCmd ()
	{
		return VBatchBase::$taskConfig->params->on2Cmd;
	}

}

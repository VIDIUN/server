<?php
/**
 * @package Scheduler
 * @subpackage Conversion.engines
 */
class VConversionEngineFfmpegAux  extends VJobConversionEngine
{
	const FFMPEG_AUX = "ffmpeg_aux";
	
	public function getName()
	{
		return self::FFMPEG_AUX;
	}
	
	public function getType()
	{
		return VidiunConversionEngineType::FFMPEG_AUX;
	}
	
	public function getCmd ()
	{
		return VBatchBase::$taskConfig->params->ffmpegAuxCmd;
	}
}

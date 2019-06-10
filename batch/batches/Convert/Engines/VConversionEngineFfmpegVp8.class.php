<?php
/**
 * @package Scheduler
 * @subpackage Conversion.engines
 */
class VConversionEngineFfmpegVp8  extends VConversionEngineFfmpeg
{
	const FFMPEG_VP8 = "ffmpeg_vp8";
	
	public function getName()
	{
		return self::FFMPEG_VP8;
	}
	
	public function getType()
	{
		return VidiunConversionEngineType::FFMPEG_VP8;
	}
	
	public function getCmd ()
	{
		return VBatchBase::$taskConfig->params->ffmpegVp8Cmd;
	}
}

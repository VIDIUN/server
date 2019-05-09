<?php
/**
 * @package Scheduler
 * @subpackage Conversion
 */
class VOperationEngineFfmpeg  extends VSingleOutputOperationEngine
{
	protected function getCmdLine()
	{
		$cmdLine=parent::getCmdLine();
		if(get_class($this)=='VOperationEngineFfmpegVp8'){
			$cmdLine=VConversionEngineFfmpeg::experimentalFixing($cmdLine, $this->data->flavorParamsOutput, $this->cmd, $this->inFilePath, $this->outFilePath);
		}
		$cmdLine=VDLOperatorFfmpeg::ExpandForcedKeyframesParams($cmdLine);
		
		// impersonite
		VBatchBase::impersonate($this->data->flavorParamsOutput->partnerId); // !!!!!!!!!!!$this->job->partnerId);

				/*
				 * Fetch watermark 
				 */
		if(isset($this->data->flavorParamsOutput->watermarkData)){
				$wmStr = $this->data->flavorParamsOutput->watermarkData;
				VidiunLog::log("watermarks:$wmStr");
				$wmData = json_decode($wmStr);
				if(isset($wmData)){
					VidiunLog::log("Watermark data:\n".print_r($wmData,1));
					$fixedCmdLine = VConversionEngineFfmpeg::buildWatermarkedCommandLine($wmData, $this->data->destFileSyncLocalPath, $cmdLine,
							VBatchBase::$taskConfig->params->ffmpegCmd, VBatchBase::$taskConfig->params->mediaInfoCmd);
					if(isset($fixedCmdLine)) $cmdLine = $fixedCmdLine;
				}
				else
					VidiunLog::err("Bad watermark JSON string($wmStr), carry on without watermark");
		}
		
				/*
				 * Fetch subtitles 
				 */
		if(isset($this->data->flavorParamsOutput->subtitlesData)){
			$subsStr = $this->data->flavorParamsOutput->subtitlesData;
			VidiunLog::log("subtitles:$subsStr");
			$subsData = json_decode($subsStr);
			if(isset($subsData)){
				$jobMsg = null;
				$fixedCmdLine = VConversionEngineFfmpeg::buildSubtitlesCommandLine($subsData, $this->data, $cmdLine, $jobMsg);
				if(isset($jobMsg)) $this->message = $jobMsg;
				if(isset($fixedCmdLine)) $cmdLine = $fixedCmdLine;
			}
			else {
				VidiunLog::err("Bad subtitles JSON string($subsStr), carry on without subtitles");
			}
		}

				/*
				 * 'watermark_pair_' tag for NGS digital signature watermarking flow
				 */
		if(isset($this->data->flavorParamsOutput->tags) && strstr($this->data->flavorParamsOutput->tags,'watermark_pair_')!=false){
			$fixedCmdLine = VConversionEngineFfmpeg::buildNGSPairedDigitalWatermarkingCommandLine($cmdLine, $this->data);
			if(isset($fixedCmdLine)) $cmdLine = $fixedCmdLine;
		}

		// un-impersonite
		VBatchBase::unimpersonate();

	
		return $cmdLine;
	}
}

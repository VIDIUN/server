<?php
/**
 * @package Scheduler
 * @subpackage Conversion
 */
class VOperationManager
{
	/**
	 * @param int $type
	 * @param VidiunConvartableJobData $data
	 * @param VidiunBatchJob $job
	 * @return VOperationEngine
	 */
	public static function getEngine($type, VidiunConvartableJobData $data, VidiunBatchJob $job)
	{
		$engine = self::createNewEngine($type, $data);
		if(!$engine)
			return null;
			
		$engine->configure($data, $job);
		return $engine;
	}
	
	/**
	 * @param int $type
	 * @param VidiunConvartableJobData $data
	 * @return VOperationEngine
	 */
	protected static function createNewEngine($type, VidiunConvartableJobData $data)
	{
		// TODO - remove after old version deprecated
		/*
		 * The 'flavorParamsOutput' is not set only for SL/ISM collections - that is definently old engine' flow
		 */		
		if(!isset($data->flavorParamsOutput) || !$data->flavorParamsOutput->engineVersion)
		{
			return new VOperationEngineOldVersionWrapper($type, $data);
		}
		
		switch($type)
		{ 
			case VidiunConversionEngineType::MENCODER:
				return new VOperationEngineMencoder(VBatchBase::$taskConfig->params->mencderCmd, $data->destFileSyncLocalPath);
				
			case VidiunConversionEngineType::ON2:
				return new VOperationEngineFlix(VBatchBase::$taskConfig->params->on2Cmd, $data->destFileSyncLocalPath);
				
			case VidiunConversionEngineType::FFMPEG:
				return new VOperationEngineFfmpeg(VBatchBase::$taskConfig->params->ffmpegCmd, $data->destFileSyncLocalPath);
				
			case VidiunConversionEngineType::FFMPEG_AUX:
				return new VOperationEngineFfmpegAux(VBatchBase::$taskConfig->params->ffmpegAuxCmd, $data->destFileSyncLocalPath);
				
			case VidiunConversionEngineType::FFMPEG_VP8:
				return new VOperationEngineFfmpegVp8(VBatchBase::$taskConfig->params->ffmpegVp8Cmd, $data->destFileSyncLocalPath);
				
			case VidiunConversionEngineType::ENCODING_COM :
				return new VOperationEngineEncodingCom(
					VBatchBase::$taskConfig->params->EncodingComUserId, 
					VBatchBase::$taskConfig->params->EncodingComUserKey, 
					VBatchBase::$taskConfig->params->EncodingComUrl);
		}
		
		if($data instanceof VidiunConvertCollectionJobData)
		{
			$engine = self::getCollectionEngine($type, $data);
			if($engine)
				return $engine;
		}
		$engine = VidiunPluginManager::loadObject('VOperationEngine', $type, array('params' => VBatchBase::$taskConfig->params, 'outFilePath' => $data->destFileSyncLocalPath));
		
		return $engine;
	}
	
	protected static function getCollectionEngine($type, VidiunConvertCollectionJobData $data)
	{
		switch($type)
		{
			case VidiunConversionEngineType::EXPRESSION_ENCODER3:
				return new VOperationEngineExpressionEncoder3(VBatchBase::$taskConfig->params->expEncoderCmd, $data->destFileName, $data->destDirLocalPath);
		}
		
		return  null;
	}
}



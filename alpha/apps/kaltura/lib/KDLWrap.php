<?php
//include_once("VDLMediaInfoLoader.php");
//include_once('VDLProcessor.php');
//include_once 'VDLUtils.php';

	/* ===========================
	 * VDLWrap
	 */
class VDLWrap
{
	public 	$_targetList = array();
	public	$_errors = array();
	public	$_warnings = array();
	public  $_rv=true;

	static $TranscodersCdl2Vdl = array(
		conversionEngineType::VIDIUN_COM=>VDLTranscoders::VIDIUN,
		conversionEngineType::ON2=>VDLTranscoders::ON2,
		conversionEngineType::FFMPEG=>VDLTranscoders::FFMPEG,
		conversionEngineType::MENCODER=>VDLTranscoders::MENCODER,
		conversionEngineType::ENCODING_COM=>VDLTranscoders::ENCODING_COM,
		conversionEngineType::FFMPEG_AUX=>VDLTranscoders::FFMPEG_AUX,
		conversionEngineType::FFMPEG_VP8=>VDLTranscoders::FFMPEG_VP8,
		conversionEngineType::EXPRESSION_ENCODER3=>VDLTranscoders::EE3,
			
		"quickTimeTools.QuickTimeTools"=>VDLTranscoders::QUICK_TIME_PLAYER_TOOLS,
		//CHUNKED_FFMPEG is a special case, it is not porcessed (yet) by the VDL
		conversionEngineType::CHUNKED_FFMPEG=>conversionEngineType::CHUNKED_FFMPEG,
	);
	
	/* ------------------------------
	 * function CDLGenerateTargetFlavors
	 */
	public static function CDLGenerateTargetFlavors($cdlMediaInfo=null, $cdlFlavorList)
	{
		$vdlWrap = new VDLWrap();
		if(!isset($cdlMediaInfo) || is_array($cdlMediaInfo)) {
			return $vdlWrap->generateTargetFlavors(null, $cdlFlavorList);
		}
		else if(get_class($cdlMediaInfo)=='mediaInfo') {
			return $vdlWrap->generateTargetFlavors($cdlMediaInfo, $cdlFlavorList);
		}
		else {
			throw new Exception("Bad argument (".get_class($cdlMediaInfo)."), should be mediaInfo class");
		}
	}
	
	/* ------------------------------
	 * function CDLGenerateTargetFlavorsCmdLinesOnly
	 */
	public static function CDLGenerateTargetFlavorsCmdLinesOnly($fileSizeKb, $cdlFlavorList)
	{
		$vdlWrap = new VDLWrap();
		if($fileSizeKb<VDLSanityLimits::MinFileSize) {
			$vdlWrap->_rv = false;
			$vdlWrap->_errors[VDLConstants::ContainerIndex][] = VDLErrors::ToString(VDLErrors::SanityInvalidFileSize, $fileSizeKb);
			return $vdlWrap;
		}
		return $vdlWrap->generateTargetFlavors(null, $cdlFlavorList);
	}
	
	/* ------------------------------
	 * function GenerateIntermediateSource
	 */
	public static function GenerateIntermediateSource(mediaInfo $cdlMediaInfo, $cdlFlavorList=null)
	{
		$mediaSet = new VDLMediaDataSet();
		self::ConvertMediainfoCdl2Mediadataset($cdlMediaInfo, $mediaSet);
		
		VidiunLog::log( "...S-->".$mediaSet->ToString());

		$profile = null;
		if(isset($cdlFlavorList)) {
			$profile = new VDLProfile();
			foreach($cdlFlavorList as $cdlFlavor) {
				$vdlFlavor = self::ConvertFlavorCdl2Vdl($cdlFlavor);
				$profile->_flavors[] = $vdlFlavor;
				VidiunLog::log( "...F-->".$vdlFlavor->ToString());
			}
		}
		
		$dlPrc = new VDLProcessor();
		
		$interSrc = $dlPrc->GenerateIntermediateSource($mediaSet, $profile);
		if(!isset($interSrc))
			return null;
		
		return self::ConvertFlavorVdl2Cdl($interSrc);
	}
	
	/* ------------------------------
	 * function generateTargetFlavors
	 */
	private function generateTargetFlavors(mediaInfo $cdlMediaInfo=null, $cdlFlavorList)
	{

		$mediaSet = new VDLMediaDataSet();
		if($cdlMediaInfo!=null) {
			self::ConvertMediainfoCdl2Mediadataset($cdlMediaInfo, $mediaSet);
		}
		VidiunLog::log( "...S-->".$mediaSet->ToString());
		
		$profile = new VDLProfile();
			/*
			 * TEMPORARY - Look for WV cases in order to disable duplicate GOP generation. After a while this will be the default behaviour  
			 */
		$isForWideVine = false;
		foreach($cdlFlavorList as $cdlFlavor) {

			$vdlFlavor = self::ConvertFlavorCdl2Vdl($cdlFlavor);
			if ($vdlFlavor->_errors)
			{
				$this->_rv = false;
				return $this;
			}
			
			if(isset($vdlFlavor->_video) && preg_match('/widevine/', strtolower($vdlFlavor->_tags), $matches)){
				$isForWideVine = true;
			}
			$profile->_flavors[] = $vdlFlavor;
			VidiunLog::log( "...F-->".$vdlFlavor->ToString());
		}

		if($isForWideVine==true) {
			foreach($profile->_flavors as $v=>$vdlFlavor) {
				if(isset($profile->_flavors[$v]->_video))
					$profile->_flavors[$v]->_video->_forWideVine = true;
			}
		}

		$trgList = array();
		{
			$dlPrc = new VDLProcessor();

			$dlPrc->Generate($mediaSet, $profile, $trgList);
			$this->_errors   = $this->_errors   + $dlPrc->get_errors();
			$this->_warnings = $this->_warnings + $dlPrc->get_warnings();
			if(count($this->_errors)>0)
				$this->_rv = false;
			else
				$this->_rv = true;
		}

		foreach ($trgList as $trg)
		{
			VidiunLog::log("...T-->" . $trg->ToString());
			/*
			 *  NOT COMMITED, to check with VDLFalvor
			 *
		if($trg->IsValid()==false && ($trg->_flags & VDLFlavor::MissingContentNonComplyFlagBit)) {
			continue;
		}
		*/
			/*
			 * Handle Chunked-Encode cases
			 */
			if ($trg->_cdlObject->getChunkedEncodeMode() == 1) {
				$tmpTrans = clone $trg->_transcoders[0];
				if($tmpTrans->_id==VDLTranscoders::FFMPEG) {
					/*
					 * Check compliance to Chunked Encoding requirements
					 */
					$vcodec = isset($trg->_video->_id)? $trg->_video->_id: null;
					$acodec = isset($trg->_audio->_id)? $trg->_audio->_id: null;
					$format = isset($trg->_container->_id)? $trg->_container->_id: null;
					$fps 	= isset($trg->_video->_frameRate)? $trg->_video->_frameRate: null;
					$gop 	= isset($trg->_video->_gop)? $trg->_video->_gop: null;
					$duration = isset($trg->_container->_duration)? round($trg->_container->_duration/1000): null;;
					$height = isset($trg->_video->_height)? $trg->_video->_height: null;
					$msgStr = null;
					$rv=VChunkedEncode::verifySupport($vcodec,$acodec,$format,$fps,$gop,$duration,$height,$msgStr);
					if($rv===true){
						$tmpTrans->_id=conversionEngineType::CHUNKED_FFMPEG;
						array_unshift($trg->_transcoders,$tmpTrans);
					}
					else {
						VidiunLog::log($msgStr);
					}
				}
			}

			$cdlFlvrOut = self::ConvertFlavorVdl2Cdl($trg);
			// Handle audio streams for ffmpeg command in case we are handling trimming a source with flavor_params -1
			// in case we need to handle multiple audio streams we need to remove the "-map_metadata -1" command
			// and replace it with the language mapping for the correct audio streams
			// if only audio streams exist without video we ignore the video mapping
			if (($cdlFlvrOut->getFlavorParamsId() == vClipAttributes::SYSTEM_DEFAULT_FLAVOR_PARAMS_ID || $cdlFlvrOut->getFlavorParamsId() === assetParamsPeer::TEMP_FLAVOR_PARAM_ID)
				&& !is_null($cdlMediaInfo))
			{
				$contentStreams = json_decode($cdlMediaInfo->getContentStreams(), true);
				$command = null;
				if ($contentStreams != null && isset($contentStreams['audio']) && count($contentStreams['audio']) > 1)
				{
					if (isset($contentStreams['video']))
						$command .= '-map v:0 ';

					$command .= '-map a ';
					foreach ($contentStreams['audio'] as $audioStream)
					{
						if (isset($audioStream['id']) && isset($audioStream['audioLanguage']))
							$command .= "-metadata:s:0:{$audioStream['id']} language={$audioStream['audioLanguage']} ";
					}
				}
				
				$cmdLines = $cdlFlvrOut->getCommandLines();
				foreach ($cmdLines as $key => $cmdLine)
				{
					if (($key == conversionEngineType::FFMPEG || $key == conversionEngineType::FFMPEG_AUX) && $command != null)
					{
						/***
						 * assetParamsPeer::TEMP_FLAVOR_PARAM_ID (-2 ) is a temporary flvor param id of type mpegts
						 * we created it for clip \ concat flow only and we do not save it to the DB
						 * in this flavor we do not have the -map_metadata -1(as it is added in VDLOperatorFfmpeg2_1_3)
						 *  but we still want to add the map section to the ffmpeg engine so we will not loose multi audio
						 * as such we concat to the '-f mpegts' the audio\video mapping
						 */
						if ($cdlFlvrOut->getFlavorParamsId() === assetParamsPeer::TEMP_FLAVOR_PARAM_ID)
						{
							$cmdLines[$key] = str_replace('-f mpegts', $command . ' -f mpegts', $cmdLine);
						}
						else
						{
							$cmdLines[$key] = str_replace('-map_metadata -1', $command, $cmdLine);
						}
					}
				}
				$cdlFlvrOut->setCommandLines($cmdLines);
			}
			$this->_targetList[] = $cdlFlvrOut;
		}


		return $this;
	}

	/* ------------------------------
	 * function CDLValidateProduct
	 */
	public static function CDLValidateProduct(mediaInfo $cdlSourceMediaInfo=null, flavorParamsOutput $cdlTarget, mediaInfo $cdlProductMediaInfo, $conversionEngine=null)
	{
		$vdlProduct = new VDLFlavor();
		VDLWrap::ConvertMediainfoCdl2Mediadataset($cdlProductMediaInfo, $vdlProduct);
		$vdlTarget = VDLWrap::ConvertFlavorCdl2Vdl($cdlTarget);
		$vdlSource = new VDLFlavor();
		// Do not run product validation when the source is undefined
		// in most cases - ForceCommand case
		if($cdlSourceMediaInfo){
			VDLWrap::ConvertMediainfoCdl2Mediadataset($cdlSourceMediaInfo, $vdlSource);
			$vdlTarget->ValidateProduct($vdlSource, $vdlProduct);
		}
		else {
			//In case we have no source media info.
			//This was added to fix cases where assets with size 0 were marked as ready. no "mediainfo" assets did not go through validation and got ready.
			//The addition of the first validation indeed caused ffmpeg flow to fail (the firs part of the volition before the OR) but the meencoder generated invalid files.  
			//The second part of the OR comes to handle cases were meencoder created faulty gray files that had only video/audio.
			if(($vdlProduct->_video===null && $vdlProduct->_audio===null) 
			|| (isset($conversionEngine) && $conversionEngine == conversionEngineType::MENCODER && !($vdlProduct->_video === null && $vdlProduct->_audio===null))) { 
				// "Invalid File - No media content.";
				$vdlProduct->_errors[VDLConstants::ContainerIndex][] = VDLErrors::ToString(VDLErrors::NoValidMediaStream);
			}
		}
		$product = VDLWrap::ConvertFlavorVdl2Cdl($vdlProduct);
		return $product;
	}

	/* ------------------------------
	 * function CDLProceessFlavorsForCollection
	 */
	public static function CDLProceessFlavorsForCollection($cdlFlavorList)
	{

		$vdlFlavorList = array();
		foreach($cdlFlavorList as $cdlFlavor) {
			$vdlFlavor = VDLWrap::ConvertFlavorCdl2Vdl($cdlFlavor);
			$vdlFlavorList[]=$vdlFlavor;
		}
		
		$xml=VDLProcessor::ProceessFlavorsForCollection($vdlFlavorList);
		VidiunLog::log(__METHOD__."-->".$xml."<--");
		foreach ($vdlFlavorList as $vdlFlavor){
			$vdlFlavor->_cdlObject->setVideoBitrate($vdlFlavor->_video->_bitRate);
		}
		return $xml;
	}

	/* ------------------------------
	 * function CDLMediaInfo2Tags
	 */
	public static function CDLMediaInfo2Tags(mediaInfo $cdlMediaInfo, $tagList) 
	{
		$mediaSet = new VDLMediaDataSet();
		self::ConvertMediainfoCdl2Mediadataset($cdlMediaInfo, $mediaSet);
		VidiunLog::log( "...S-->".$mediaSet->ToString());
		$tagsOut = array();
		$tagsOut = $mediaSet->ToTags($tagList);
		return $tagsOut;
	}
	
	/* ------------------------------
	 * function CDLIsFLV
	 */
	public static function CDLIsFLV(mediaInfo $cdlMediaInfo) 
	{
		$tagList[] = "flv";
		$mediaSet = new VDLMediaDataSet();
		self::ConvertMediainfoCdl2Mediadataset($cdlMediaInfo, $mediaSet);
		VidiunLog::log("...S-->".$mediaSet->ToString());
		$tagsOut = array();
		$tagsOut = $mediaSet->ToTags($tagList);
		if(count($tagsOut)==1) {
			return true;
		}
		else {
			return false;
		}
	}
	
	/* ------------------------------
	 * function ConvertFlavorVdl2Cdl
	 */
	public static function ConvertFlavorVdl2Cdl(VDLFlavor $target){
		$flavor = new flavorParamsOutputWrap();

		$flavor->setFlavorParamsId($target->_id);
		$flavor->setName($target->_name);
		$flavor->setType($target->_type);
		$flavor->setTags($target->_tags);
		
		if($target->_cdlObject)
		{
			$flavor->setReadyBehavior($target->_cdlObject->getReadyBehavior());
			$flavor->setSourceRemoteStorageProfileId($target->_cdlObject->getSourceRemoteStorageProfileId());
			$flavor->setRemoteStorageProfileIds($target->_cdlObject->getRemoteStorageProfileIds());
			$flavor->setMediaParserType($target->_cdlObject->getMediaParserType());
			$flavor->setSourceAssetParamsIds($target->_cdlObject->getSourceAssetParamsIds());
		}
		
		if($target->IsRedundant()) {
			$flavor->_isRedundant = true;
		}
		else {
			$flavor->_isRedundant = false;
		}
		
		if($target->IsNonComply()) {
			$flavor->_isNonComply = true;
		}
		else {
			$flavor->_isNonComply = false;
		}
		if($target->_clipStart)
			$flavor->setClipOffset($target->_clipStart);
		if($target->_clipDur)
			$flavor->setClipDuration($target->_clipDur);
/**/
		$flavor->_isEncrypted = $target->_isEncrypted;

		if(isset($target->_multiStream))
		{
			$toJson = json_encode($target->_multiStream);
			$flavor->setMultiStream($toJson);
			/*
			 * Audio-only flavors w/multi-lingual setup, might get wiped out by bitrate-optimization logic.
			 * To avoid this - turn such flavors into 'forced'.
			 */
			if(isset($target->_audio) && !isset($target->_video)
			&& isset($target->_multiStream->audio->streams) && count($target->_multiStream->audio->streams)>0){
				$flavor->_force = true;
			}
		}

		$flavor->_errors   = $flavor->_errors + $target->_errors;
		$flavor->_warnings = $flavor->_warnings + $target->_warnings;
		
		if($target->_container)
			$flavor->setFormat($target->_container->GetIdOrFormat());
		
		if($target->_video) {
			//echo "\n target->_video - "; print_r($target->_video); echo "\n";
			$flavor->setVideoCodec($target->_video->GetIdOrFormat());
			$flavor->setVideoBitrate($target->_video->_bitRate);
			$flavor->setWidth($target->_video->_width);
			$flavor->setHeight($target->_video->_height);
			$flavor->setFrameRate($target->_video->_frameRate);
			$flavor->setGopSize($target->_video->_gop);
			if($target->_video->_arProcessingMode)
				$flavor->setAspectRatioProcessingMode($target->_video->_arProcessingMode);
			if($target->_video->_forceMult16)
				$flavor->setForceFrameToMultiplication16($target->_video->_forceMult16);
			if(isset($target->_video->_contentAwareness))
			 	$flavor->setContentAwareness($target->_video->_contentAwareness);
			/*
			 * Watermark
			 */
			if(isset($target->_video->_watermarkData))
			{
				$toJson = json_encode($target->_video->_watermarkData);
				$flavor->setWatermarkData($toJson);
			}
				
			/*
			 * Subtitled
			 */
			if(isset($target->_video->_subtitlesData))
			{
				$toJson = json_encode($target->_video->_subtitlesData);
				$flavor->setSubtitlesData($toJson);
			}
		}

		if($target->_audio) {
			$flavor->setAudioCodec($target->_audio->GetIdOrFormat());
			$flavor->setAudioBitrate($target->_audio->_bitRate);
			$flavor->setAudioChannels($target->_audio->_channels);
			$flavor->setAudioSampleRate($target->_audio->_sampleRate);
		}
		
		if($target->_pdf)
			$flavor->putInCustomData('readonly',$target->_pdf->_readonly);
		
		$cdlOprSets = VDLWrap::convertOperatorsVdl2Cdl($target->_transcoders);
		if($target->_engineVersion==1) {
			VidiunLog::log("\noperators==>\n".print_r($cdlOprSets,true));
			$flavor->setOperators($cdlOprSets->getSerialized());
			$flavor->setEngineVersion(1);
		}
		else {
			$flavor->setEngineVersion(0);
			$convEnginesAssociated = null;
			$commandLines = array();
			foreach($target->_transcoders as $key => $transObj) {
				$extra = $transObj->_extra;
	
					/* -------------------------
					 * Translate VDL transcoders enums to CDL
					 */
				$str = null;
				$cdlTrnsId=array_search($transObj->_id,self::$TranscodersCdl2Vdl);
				if($cdlTrnsId!==false){
					$str = $cdlTrnsId;
					$commandLines[$cdlTrnsId]=$transObj->_cmd;
				}
				
					// Add qt-faststart processing for mp4 targets (relevant to pre-opertors mode) 
				if($flavor->getFormat()=="mp4" 
				&& in_array($cdlTrnsId, array(conversionEngineType::FFMPEG, conversionEngineType::CHUNKED_FFMPEG, 
							      conversionEngineType::FFMPEG_AUX, conversionEngineType::FFMPEG_VP8, conversionEngineType::MENCODER))){
					$fsAddonStr = vConvertJobData::CONVERSION_MILTI_COMMAND_LINE_SEPERATOR.vConvertJobData::CONVERSION_FAST_START_SIGN;
					$commandLines[$cdlTrnsId].=$fsAddonStr;
				}
				
				if($convEnginesAssociated!==null) {
					$convEnginesAssociated = $convEnginesAssociated.",".$str;
				}
				else {
					$convEnginesAssociated = $str;
				}					
	//echo "transcoder-->".$key." flag:".$flag." str:".$trnsStr."<br>\n";
				
			}
			$flavor->setCommandLines($commandLines);
			$flavor->setConversionEngines($convEnginesAssociated);
		}
		$flavor->setFileExt($target->EvaluateFileExt());
		$flavor->_errors = $flavor->_errors + $target->_errors;
		//echo "target errs "; print_r($target->_errors);
		//echo "flavor errs "; print_r($flavor->_errors);
		$flavor->_warnings = $flavor->_warnings + $target->_warnings;
		//echo "target wrns "; print_r($target->_warnings);
		//echo "flavor wrns "; print_r($flavor->_warnings);
		
		//echo "flavor "; print_r($flavor);
		
		//VidiunLog::log(__METHOD__."\nflavorOutputParams==>\n".print_r($flavor,true));
		return $flavor;
	}
	
	/* ------------------------------
	 * function ConvertFlavorCdl2Vdl
	 */
	public static function ConvertFlavorCdl2Vdl($cdlFlavor)
	{
		$vdlFlavor = new VDLFlavor();
		
		$vdlFlavor->_name = $cdlFlavor->getName();
		$vdlFlavor->_id = $cdlFlavor->getId();
		$vdlFlavor->_type = $cdlFlavor->getType();
		$vdlFlavor->_tags = $cdlFlavor->getTags();
		if($cdlFlavor instanceof flavorParams)
		{ 
			$vdlFlavor->_clipStart = $cdlFlavor->getClipOffset();
			$vdlFlavor->_clipDur = $cdlFlavor->getClipDuration();
/**/
			$multiStream = $cdlFlavor->getMultiStream();
			if(isset($multiStream)) {
						//Sample json string: {"detect":"auto","audio":{"mapping":[1,2]}}
				$fromJson = json_decode($multiStream);
				$vdlFlavor->_multiStream = isset($fromJson)? $fromJson: null;
			}
			$vdlFlavor->_optimizationPolicy = $cdlFlavor->getOptimizationPolicy();
			/*
			 * 'IsEncrypted' was switched from true/false flag vals,
			 * to 'mode' values 
			 *	0:don't encrypt (equivalent to original 'false')
			 *	1:encrypt contents >10sec (equivalent to original 'true')
			 *	2:encrypt all 
			 */
			if(($tmpEncrypted=$cdlFlavor->getIsEncrypted())){
				if($tmpEncrypted===true)
					$vdlFlavor->_isEncrypted = 1; 
				else if($tmpEncrypted>0)
					$vdlFlavor->_isEncrypted = $tmpEncrypted;
			}
		}
		else if($cdlFlavor instanceof flavorParamsOutput){
			$vdlFlavor->_clipStart = $cdlFlavor->getClipOffset();
			$vdlFlavor->_clipDur = $cdlFlavor->getClipDuration();		
		}
		
		$vdlFlavor->_cdlObject = $cdlFlavor;
			/* 
			 * Media container initialization
			 */	
		{
			$vdlFlavor->_container = new VDLContainerData();
			$vdlFlavor->_container->_id=$cdlFlavor->getFormat();
	//		$vdlFlavor->_container->_duration=$api->getContainerDuration();
	//		$vdlFlavor->_container->_bitRate=$api->getContainerBitRate();
	//		$vdlFlavor->_container->_fileSize=$api->getFileSize();
			if($vdlFlavor->_container->IsDataSet()==false)
				$vdlFlavor->_container = null;
		}
			/* 
			 * Video stream initialization
			 */	
		{
			$vdlFlavor->_video = new VDLVideoData();
			$vdlFlavor->_video->_id = $cdlFlavor->getVideoCodec();
	//		$vdlFlavor->_video->_format = $api->getVideoFormat();
	//		$vdlFlavor->_video->_duration = $api->getVideoDuration();
			$vdlFlavor->_video->_bitRate = $cdlFlavor->getVideoBitRate();
			$vdlFlavor->_video->_width = $cdlFlavor->getWidth();
			$vdlFlavor->_video->_height = $cdlFlavor->getHeight();
			$vdlFlavor->_video->_frameRate = $cdlFlavor->getFrameRate();
			$vdlFlavor->_video->_gop = $cdlFlavor->getGopSize();
			$vdlFlavor->_isTwoPass = $cdlFlavor->getTwoPass();
			$vdlFlavor->_video->_arProcessingMode = $cdlFlavor->getAspectRatioProcessingMode();
			$vdlFlavor->_video->_forceMult16 = $cdlFlavor->getForceFrameToMultiplication16();
			if($cdlFlavor instanceof flavorParams) {
				$vdlFlavor->_video->_cbr = $cdlFlavor->getVideoConstantBitrate();
				$vdlFlavor->_video->_bt = $cdlFlavor->getVideoBitrateTolerance();
				$vdlFlavor->_video->_isGopInSec = $cdlFlavor->getIsGopInSec();
				$vdlFlavor->_video->_isShrinkFramesizeToSource = !$cdlFlavor->getIsAvoidVideoShrinkFramesizeToSource();
				$vdlFlavor->_video->_isShrinkBitrateToSource   = !$cdlFlavor->getIsAvoidVideoShrinkBitrateToSource();
				$vdlFlavor->_video->_isFrameRateForLowBrAppleHls = $cdlFlavor->getIsVideoFrameRateForLowBrAppleHls();
				$vdlFlavor->_video->_anamorphic = $cdlFlavor->getAnamorphicPixels();
				$vdlFlavor->_video->_maxFrameRate = $cdlFlavor->getMaxFrameRate();
//				$vdlFlavor->_video->_isForcedKeyFrames = !$cdlFlavor->getIsAvoidForcedKeyFrames();
					/*
					 * 'getForcedKeyFramesMode' should be used instead of obsolete 'getIsAvoidForcedKeyFrames' is obsolete.
					 * But for backward compatibility (till switching of existing non-default settings to new 'getForcedKeyFramesMode'),
					 * check both fields
					 */
				if($cdlFlavor->getIsAvoidForcedKeyFrames()!=0)
					$vdlFlavor->_video->_forcedKeyFramesMode = 0;
				else
					$vdlFlavor->_video->_forcedKeyFramesMode = $cdlFlavor->getForcedKeyFramesMode();

				$vdlFlavor->_video->_isCropIMX = $cdlFlavor->getIsCropIMX();
				$vdlFlavor->_video->_contentAwareness = $cdlFlavor->getContentAwareness();
					/*
					 * Due to multiple WM support,
					 * the single WM settings is turned into array as well
					 */
				$watermarkData = $cdlFlavor->getWatermarkData();
				if(isset($watermarkData)) {
					$fromJson = json_decode($watermarkData);
					if(isset($fromJson)){
						if(!is_array($fromJson)){
							$vdlFlavor->_video->_watermarkData = array($fromJson);
						}
						else {
							$vdlFlavor->_video->_watermarkData = $fromJson;
						}
					}
					else
						$vdlFlavor->_video->_watermarkData = null;
				}
				
					/*
					 * Subtitles
					 */
				$subtitlesData = $cdlFlavor->getSubtitlesData();
				if(isset($subtitlesData)) {
					$fromJson = json_decode($subtitlesData);
					if(isset($fromJson)){
						$vdlFlavor->_video->_subtitlesData = $fromJson;
					}
					else{
						$vdlFlavor->_video->_subtitlesData = null;
					}
				}
			}
			
			if($vdlFlavor->_video->IsDataSet()==false)
				$vdlFlavor->_video = null;
		}
		
			/* 
			 * Audio stream initialization
			 */	
		{
			$vdlFlavor->_audio = new VDLAudioData();
			$vdlFlavor->_audio->_id = $cdlFlavor->getAudioCodec();
	//		$flavor->_audio->_format = $cdlFlavor->getAudioFormat();
	//		$flavor->_audio->_duration = $cdlFlavor->getAudioDuration();
			$vdlFlavor->_audio->_bitRate = $cdlFlavor->getAudioBitRate();
			$vdlFlavor->_audio->_channels = $cdlFlavor->getAudioChannels();
			$vdlFlavor->_audio->_sampleRate = $cdlFlavor->getAudioSampleRate();
			$vdlFlavor->_audio->_resolution = $cdlFlavor->getAudioResolution();
			if($vdlFlavor->_audio->IsDataSet()==false)
				$vdlFlavor->_audio = null;
		}
		$operators = $cdlFlavor->getOperators();
		$transObjArr = array();
		//VidiunLog::log(__METHOD__."\nCDL Flavor==>\n".print_r($cdlFlavor,true));
		if(!empty($operators) || $cdlFlavor->getEngineVersion()==1) {
			$transObjArr = VDLWrap::convertOperatorsCdl2Vdl($operators);
			$vdlFlavor->_engineVersion = 1;
		}
		else {
			$vdlFlavor->_engineVersion = 0;
			$trnsStr = $cdlFlavor->getConversionEngines();
			$extraStr = $cdlFlavor->getConversionEnginesExtraParams();
			$transObjArr=VDLUtils::parseTranscoderList($trnsStr, $extraStr);
			if($cdlFlavor instanceof flavorParamsOutputWrap || $cdlFlavor instanceof flavorParamsOutput) {
				$cmdLines = $cdlFlavor->getCommandLines();
				foreach($transObjArr as $transObj){
					$transObj->_cmd = $cmdLines[$transObj->_id];
				}
			}
			VidiunLog::log("\ntranscoders==>\n".print_r($transObjArr,true));
		}

		VDLUtils::RecursiveScan($transObjArr, "transcoderSetFuncWrap", self::$TranscodersCdl2Vdl, "");
		$vdlFlavor->_transcoders = $transObjArr;
		
		if($cdlFlavor instanceof flavorParamsOutputWrap) {
			if($cdlFlavor->_isRedundant) {
				$vdlFlavor->_flags = $vdlFlavor->_flags | VDLFlavor::RedundantFlagBit;
			}
			if($cdlFlavor->_isNonComply) {
				$vdlFlavor->_flags = $vdlFlavor->_flags | VDLFlavor::BitrateNonComplyFlagBit;
			}
			$vdlFlavor->_errors = $vdlFlavor->_errors + $cdlFlavor->_errors;
			$vdlFlavor->_warnings = $vdlFlavor->_warnings + $cdlFlavor->_warnings;
		}
		
		if($cdlFlavor instanceof SwfFlavorParams || $cdlFlavor instanceof SwfFlavorParamsOutput) {
			$vdlFlavor->_swf = new VDLSwfData();
			$vdlFlavor->_swf->_flashVersion = $cdlFlavor->getFlashVersion();
			$vdlFlavor->_swf->_zoom         = $cdlFlavor->getZoom();
			$vdlFlavor->_swf->_zlib         = $cdlFlavor->getZlib();
			$vdlFlavor->_swf->_jpegQuality  = $cdlFlavor->getJpegQuality();
			$vdlFlavor->_swf->_sameWindow   = $cdlFlavor->getSameWindow();
			$vdlFlavor->_swf->_insertStop   = $cdlFlavor->getInsertStop();
			$vdlFlavor->_swf->_useShapes    = $cdlFlavor->getUseShapes();
			$vdlFlavor->_swf->_storeFonts   = $cdlFlavor->getStoreFonts();
			$vdlFlavor->_swf->_flatten      = $cdlFlavor->getFlatten();
			$vdlFlavor->_swf->_poly2Bitmap	= $cdlFlavor->getPoly2bitmap();
		}
		
		if($cdlFlavor instanceof PdfFlavorParams || $cdlFlavor instanceof PdfFlavorParamsOutput) {
			$vdlFlavor->_pdf = new VDLPdfData();
			$vdlFlavor->_pdf->_resolution  = $cdlFlavor->getResolution();
			$vdlFlavor->_pdf->_paperHeight = $cdlFlavor->getPaperHeight();
			$vdlFlavor->_pdf->_paperWidth  = $cdlFlavor->getPaperWidth();
			$vdlFlavor->_pdf->_readonly  = $cdlFlavor->getReadonly();
		}
		if($cdlFlavor instanceof ImageFlavorParams || $cdlFlavor instanceof ImageFlavorParamsOutput) {
			$vdlFlavor->_image = new VDLImageData();
			$vdlFlavor->_image->_densityWidth = $cdlFlavor->getDensityWidth();
			$vdlFlavor->_image->_densityHeight = $cdlFlavor->getDensityHeight();
			$vdlFlavor->_image->_sizeWidth = $cdlFlavor->getSizeWidth();
			$vdlFlavor->_image->_sizeHeight = $cdlFlavor->getSizeHeight();
			$vdlFlavor->_image->_depth = $cdlFlavor->getDepth();
			$vdlFlavor->_image->_format = $cdlFlavor->getFormat();
		}
		
		
		//VidiunLog::log(__METHOD__."\nVDL Flavor==>\n".print_r($vdlFlavor,true));
		if(is_null($vdlFlavor->_container))
		{
			VidiunLog::log("No Container Found On Flavor Convert Will Fail");
			$vdlFlavor->_errors[VDLConstants::ContainerIndex][] = VDLErrors::ToString(VDLErrors::InvalidFlavorParamConfiguration);
		}
		return $vdlFlavor;
	}
	
	/* ------------------------------
	 * function ConvertMediainfoCdl2Mediadataset
	 */
	public static function ConvertMediainfoCdl2Mediadataset(mediaInfo $cdlMediaInfo, VDLMediaDataSet &$medSet)
	{
		$medSet->_container = new VDLContainerData();
/**/
		$contentStreams = $cdlMediaInfo->getContentStreams();
		if(isset($contentStreams)) {
			if(is_string($contentStreams)) {
				$fromJson = json_decode($contentStreams);
				$medSet->_contentStreams = isset($fromJson)? $fromJson: null;
			}
			else {
				$medSet->_contentStreams = $contentStreams;
			}
		}

		/*
		 * Auto-decryption 
		 */
		if(isset($cdlMediaInfo->decryptionKey)){
			$medSet->_decryptionKey = $cdlMediaInfo->decryptionKey;
		}
	$medSet->_container->_id=$cdlMediaInfo->getContainerId();
		$medSet->_container->_format=$cdlMediaInfo->getContainerFormat();
		$medSet->_container->_duration=$cdlMediaInfo->getContainerDuration();
		$medSet->_container->_bitRate=$cdlMediaInfo->getContainerBitRate();
		$medSet->_container->_fileSize=$cdlMediaInfo->getFileSize();
		$medSet->_container->_isFastStart=$cdlMediaInfo->getIsFastStart();
		if($medSet->_container->IsDataSet()==false)
			$medSet->_container = null;

		$medSet->_video = new VDLVideoData();
		$medSet->_video->_id = $cdlMediaInfo->getVideoCodecId();
		$medSet->_video->_format = $cdlMediaInfo->getVideoFormat();
		$medSet->_video->_duration = $cdlMediaInfo->getVideoDuration();
		$medSet->_video->_bitRate = $cdlMediaInfo->getVideoBitRate();
		$medSet->_video->_width = $cdlMediaInfo->getVideoWidth();
		$medSet->_video->_height = $cdlMediaInfo->getVideoHeight();
		$medSet->_video->_frameRate = $cdlMediaInfo->getVideoFrameRate();
		$medSet->_video->_dar = $cdlMediaInfo->getVideoDar();
		$medSet->_video->_rotation = $cdlMediaInfo->getVideoRotation();
		$medSet->_video->_scanType = $cdlMediaInfo->getScanType();
		$medSet->_video->_complexityValue = $cdlMediaInfo->getComplexityValue();
		$medSet->_video->_gop = $cdlMediaInfo->getMaxGOP();
/*		{
				$medLoader = new VDLMediaInfoLoader($cdlMediaInfo->getRawData());
				$md = new VDLMediadataset();
				$medLoader->Load($md);
				if($md->_video)
					$medSet->_video->_scanType = $md->_video->_scanType;
		}
*/
		if($medSet->_video->IsDataSet()==false)
			$medSet->_video = null;

		$medSet->_audio = new VDLAudioData();
		$medSet->_audio->_id = $cdlMediaInfo->getAudioCodecId();
		$medSet->_audio->_format = $cdlMediaInfo->getAudioFormat();
		$medSet->_audio->_duration = $cdlMediaInfo->getAudioDuration();
		$medSet->_audio->_bitRate = $cdlMediaInfo->getAudioBitRate();
		$medSet->_audio->_channels = $cdlMediaInfo->getAudioChannels();
		$medSet->_audio->_sampleRate = $cdlMediaInfo->getAudioSamplingRate();
		$medSet->_audio->_resolution = $cdlMediaInfo->getAudioResolution();
		if($medSet->_audio->IsDataSet()==false)
			$medSet->_audio = null;

		return $medSet;
	}

	/* ------------------------------
	 * function ConvertMediainfoCdl2Mediadataset
	 */
	public static function ConvertMediainfoCdl2FlavorAsset(mediaInfo $cdlMediaInfo, flavorAsset &$fla)
	{
		VidiunLog::log("CDL mediaInfo==>\n".print_r($cdlMediaInfo,true));
	  	$medSet = new VDLMediaDataSet();
		self::ConvertMediainfoCdl2Mediadataset($cdlMediaInfo, $medSet);
		VidiunLog::log("VDL mediaDataSet==>\n".print_r($medSet,true));

		$contBr = 0;
		if(isset($medSet->_container)){
			$fla->setContainerFormat($medSet->_container->GetIdOrFormat());
			$contBr = $medSet->_container->_bitRate;
		}
  		$fla->setSize($cdlMediaInfo->getFileSize());

		$vidBr = 0;
		if(isset($medSet->_video)){
			$fla->setWidth($medSet->_video->_width);
  			$fla->setHeight($medSet->_video->_height);
  			$fla->setFrameRate($medSet->_video->_frameRate);
			$vidBr = $medSet->_video->_bitRate;
			$fla->setVideoCodecId($medSet->_video->GetIdOrFormat());
		}
		$audBr = 0;
		if(isset($medSet->_audio)){
			$audBr = $medSet->_audio->_bitRate;
		}
		/*
		 * Evaluate the asset br.
		 * Prevously it was taken from video, if t was available.
		 */
		$assetBr = max($contBr,$vidBr+$audBr);
		$fla->setBitrate($assetBr);
		$fla->setContainsAudio($cdlMediaInfo->isContainAudio());
		/*
		 * Set flavorAsset language to mediaInfo first audio language
		 */
		if(isset($medSet->_contentStreams->audio) && isset($medSet->_contentStreams->audio[0]->audioLanguage)){
			$lang = $medSet->_contentStreams->audio[0]->audioLanguage;
			VidiunLog::log("Flavor asset(".$fla->getId().") language updated to ($lang)");
			$fla->setLanguage($lang);
		}

		VidiunLog::log("CDL fl.Asset==>\n".print_r($fla,true));
		return $fla;
	}

	/* ------------------------------
	 * function convertOperatorsCdl2Vdl
	 */
	public static function convertOperatorsCdl2Vdl($operators)
	{
		VidiunLog::log("\ncdlOperators==>\n".print_r($operators,true));
		$transObjArr = array();
		$oprSets = new vOperatorSets();
		//		$operators = stripslashes($operators);
		//VidiunLog::log(__METHOD__."\ncdlOperators(stripslsh)==>\n".print_r($operators,true));
		$oprSets->setSerialized($operators);
		VidiunLog::log("\noperatorSets==>\n".print_r($oprSets,true));
		foreach ($oprSets->getSets() as $oprSet) {
			if(count($oprSet)==1) {
				$opr = $oprSet[0];
				VidiunLog::log("\n1==>\n".print_r($oprSet,true));
				$vdlOpr = new VDLOperationParams($opr);
				$transObjArr[] = $vdlOpr;
			}
			else {
				$auxArr = array();
				foreach ($oprSet as $opr) {
					VidiunLog::log("\n2==>\n".print_r($oprSet,true));
					$vdlOpr = new VDLOperationParams($opr);
					$auxArr[] = $vdlOpr;
				}
				$transObjArr[] = $auxArr;
			}
		}
		return $transObjArr;
	}

	/* ------------------------------
	 * function convertOperatorVdl2Cdl
	 */
	public static function convertOperatorVdl2Cdl($vdlOperator, $id=null)
	{
		$opr = new vOperator();
		if(!$id || $id===false)
			$opr->id = $vdlOperator->_id;
		else
			$opr->id = $id;
		
		$opr->extra = $vdlOperator->_extra;
		$opr->command = $vdlOperator->_cmd;
		$opr->config = $vdlOperator->_cfg;
		$opr->params = $vdlOperator->_params;
		$opr->isOptional = $vdlOperator->_isOptional;
		return $opr;
	}
	
	/* ------------------------------
	 * function convertOperatorsVdl2Cdl
	 */
	public static function convertOperatorsVdl2Cdl($vdlOperators)
	{
	$cdlOprSets = new vOperatorSets();
		foreach($vdlOperators as $transObj) {
			$auxArr = array();
			if(is_array($transObj)) {
				foreach($transObj as $tr) {
					$key=array_search($tr->_id,self::$TranscodersCdl2Vdl);
//					$opr = new vOperator();
//					if($key===false)
//						$opr->id = $tr->_id;
//					else
//						$opr->id = $key;
//					$opr->extra = $tr->_extra;
//					$opr->command = $tr->_cmd;
//					$opr->config = $tr->_cfg;
//					$auxArr[] = $opr;
					$auxArr[] = VDLWrap::convertOperatorVdl2Cdl($tr, $key);
				}
			}
			else {
				$key=array_search($transObj->_id,self::$TranscodersCdl2Vdl);
//				$opr = new vOperator();
//				if($key===false)
//					$opr->id = $transObj->_id;
//				else
//					$opr->id = $key;
//				$opr->extra = $transObj->_extra;
//				$opr->command = $transObj->_cmd;
//				$opr->config = $transObj->_cfg;
//				$auxArr[] = $opr;
				$auxArr[] = VDLWrap::convertOperatorVdl2Cdl($transObj, $key);
			}
			$cdlOprSets->addSet($auxArr);
		}
		return $cdlOprSets;
	}
}

	/* ===========================
	 * flavorParamsOutputWrap
	 */
class flavorParamsOutputWrap extends flavorParamsOutput {

	/* ---------------------
	 * Data
	 */
	public  $_isRedundant=false;
	public 	$_isNonComply=false;
	public 	$_force=false;
	public	$_create_anyway=false;
	public	$_passthrough = false;		// true: skip execution of this engine,use the source for output.
	
	public  $_errors=array(),
			$_warnings=array();

	/* ------------------------------
	 * IsValid
	 */
	public function IsValid()
	{
		return (count($this->_errors)==0);
	}
		
}

		/* ---------------------------
		 * transcoderSetFuncWrap
		 */
function transcoderSetFuncWrap($oprObj, $transDictionary, $param2)
{
	$trId = VDLUtils::trima($oprObj->_id);
	if(!is_null($transDictionary) && array_key_exists($trId, $transDictionary)){
		$oprObj->_id = $transDictionary[$trId];
	}

//	$oprObj->_engine = VDLWrap::GetEngineObject($oprObj->_id);
	$id = $oprObj->_id;
	VidiunLog::log(":operators id=$id :");
	$engine=null;
	if(isset($oprObj->_className) && class_exists($oprObj->_className)){
		try {
			$engine = new $oprObj->_className($id);
		}
		catch(Exception $e){
			$engine=null;
		}
	}
	
	if(isset($engine)) {
		VidiunLog::log(__METHOD__.": the engine was successfully overloaded with $oprObj->_className");
	}
	else {
		switch($id){
		case VDLTranscoders::VIDIUN:
		case VDLTranscoders::ON2:
		case VDLTranscoders::FFMPEG:
		case VDLTranscoders::MENCODER:
		case VDLTranscoders::ENCODING_COM:
		case VDLTranscoders::FFMPEG_AUX:
		case VDLTranscoders::FFMPEG_VP8:
		case VDLTranscoders::EE3:
			$engine = new VDLOperatorWrapper($id);
			break;
		case VDLTranscoders::QUICK_TIME_PLAYER_TOOLS:
			$engine = VidiunPluginManager::loadObject('VDLOperatorBase', "quickTimeTools.QuickTimeTools");
			break;
		default:
//		VidiunLog::log("in default :operators id=$id :");
			$engine = VidiunPluginManager::loadObject('VDLOperatorBase', $id);
			break;
		}
	}

	if(is_null($engine)) {
		VidiunLog::log(__METHOD__.":ERROR - plugin manager returned with null");
	}
	else {
		$oprObj->_engine = $engine;
		VidiunLog::log(__METHOD__."Engine object from plugin mgr==>\n".print_r($oprObj->_engine,true));
	}
	
	return;
}

?>

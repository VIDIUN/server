<?php
/**
 * @package Scheduler
 * @subpackage Conversion.engines
 */
class VConversionEngineFfmpeg  extends VJobConversionEngine
{
	const FFMPEG = "ffmpeg";
	
	const TAG_VARIANT_A = 'watermark_a';
	const TAG_VARIANT_B = 'watermark_b';
	const TAG_VARIANT_PAIR_ID = 'watermark_pair_';
	const TAG_NGS_STUB = "stub";

	public function getName()
	{
		return self::FFMPEG;
	}
	
	public function getType()
	{
		return VidiunConversionEngineType::FFMPEG;
	}
	
	public function getCmd ()
	{
		return VBatchBase::$taskConfig->params->ffmpegCmd;
	}
	
	/**
	 *
	 */
	protected function getExecutionCommandAndConversionString ( VidiunConvertJobData $data )
	{
		$wmData = null;
		if(isset($data->flavorParamsOutput->watermarkData)){
			$wmData = json_decode($data->flavorParamsOutput->watermarkData);
			if(!isset($wmData)){
				VidiunLog::notice("Bad watermark JSON string($data->flavorParamsOutput->watermarkData), carry on without watermark");
			}
		}
		$subsData = null;
		if(isset($data->flavorParamsOutput->subtitlesData)){
			$subsData = json_decode($data->flavorParamsOutput->subtitlesData);
			if(!isset($subsData)){
				VidiunLog::notice("Bad subtitles JSON string(".$data->flavorParamsOutput->subtitlesData."), carry on without subtitles");
			}
		}
		$cmdLines =  parent::getExecutionCommandAndConversionString ($data);
		VidiunLog::log("cmdLines==>".print_r($cmdLines,1));
			/*
			 * The code below handles the ffmpeg 0.10 and higher option to set up 'forced_key_frame'.
			 * The ffmpeg cmd-line should contain list of all forced kf's, this list might be up to 40Kb for 2hr videos.
			 * Since the cmd-lines are stored in db records (flavor_params_output), it would blow it up.
			 * The solution is to setup a placeholer w/duration and step, the full cmd-line is generated over here
			 * just before the activation.
			 * Sample:
			 *    	__forceKeyframes__462_2
			 *		stands for duration of 462 seconds, gop size 2 seconds
			 */
		foreach($cmdLines as $k=>$cmdLine){
			if(VConversionEngineFfmpegVp8::FFMPEG_VP8==$this->getName()){
				$exec_cmd = self::experimentalFixing($cmdLine->exec_cmd, $data->flavorParamsOutput, $this->getCmd(), $this->inFilePath, $this->outFilePath);
			}
			else $exec_cmd = $cmdLine->exec_cmd;
			$exec_cmd = VDLOperatorFfmpeg::ExpandForcedKeyframesParams($exec_cmd);
			
			if(strstr($exec_cmd, "ffmpeg")==false) {
				$cmdLines[$k]->exec_cmd = $exec_cmd;
				continue;
			}
			
				// impersonite
			VBatchBase::impersonate($data->flavorParamsOutput->partnerId);
			
				/*
				 * Fetch watermark (visible, not forensic ...) 
				 */
			if(isset($wmData)){
				$fixedCmdLine = self::buildWatermarkedCommandLine($wmData, $data->destFileSyncLocalPath, $exec_cmd, 
								VBatchBase::$taskConfig->params->ffmpegCmd, VBatchBase::$taskConfig->params->mediaInfoCmd);
				if(isset($fixedCmdLine)) $exec_cmd = $fixedCmdLine;
			}
			
				/*
				 * Fetch subtitles 
				 */
			if(isset($subsData)){
				$jobMsg = null;
				$fixedCmdLine = self::buildSubtitlesCommandLine($subsData, $data, $exec_cmd, $jobMsg);
				if(isset($jobMsg)) $this->message = $jobMsg;
				if(isset($fixedCmdLine)) $exec_cmd = $fixedCmdLine;
			}
				/*
				 * 'watermark_pair_'/TAG_VARIANT_PAIR_ID tag for NGS digital/forensic signature watermarking flow
				 */
			if(isset($data->flavorParamsOutput->tags) && strstr($data->flavorParamsOutput->tags,VConversionEngineFfmpeg::TAG_VARIANT_PAIR_ID)!=false){
				$fixedCmdLine = self::buildNGSPairedDigitalWatermarkingCommandLine($exec_cmd, $data);
				if(isset($fixedCmdLine)) $exec_cmd = $fixedCmdLine;
			}
				// un-impersonite
			VBatchBase::unimpersonate();
				
			$cmdLines[$k]->exec_cmd = $exec_cmd;
			
		}
		return $cmdLines;
	}
	
	/**
	 * 
	 * @param unknown_type $cmdStr
	 * @param unknown_type $flavorParamsOutput
	 * @param unknown_type $binCmd
	 * @param unknown_type $srcFilePath
	 * @param unknown_type $outFilePath
	 * @return unknown|string
	 */
	public static function experimentalFixing($cmdStr, $flavorParamsOutput, $binCmd, $srcFilePath, $outFilePath)
	{
/*
 * Samples - 
 * Original 
 * ffmpeg -i SOURCE 
 * 	   -c:v libx265 
 * 		-pix_fmt yuv420p -aspect 640:360 -b:v 8000k -s 640x360 -r 30 -g 60 
 * 		-c:a libfdk_aac -b:a 128k -ar 44100 -f mp4 -y OUTPUT
 * 
 * Switched - 
 * ffmpeg -i SOURCE 
 * 		-pix_fmt yuv420p -aspect 640:360 -b:v 8000k -s 640x360 -r 30 -g 60 -f yuv4mpegpipe -an - 
 * 		-vn 
 * 		-c:a libfdk_aac -b:a 128k -ar 44100 -f mp4 -y OUTPUT.aac 
 * 		| /home/dev/x265 - --y4m --scenecut 40 --keyint 60 --min-keyint 1 --bitrate 2000 --qpfile OUTPUT.qp OUTPUT.h265 
 * 		&& ~/ffmpeg-2.4.3 -i OUTPUT.aac -r 30 -i OUTPUT.h265 -c copy -f mp4 -y OUTPUT
 * 
 */

		/*
		 * New binaries/aliases on transcoding servers
		 */
$x265bin = "x265";
$ffmpegExperimBin = "ffmpeg-experim";

		if($flavorParamsOutput->videoCodec==VDLVideoTarget::H265){ //video_codec	!!!flavorParamsOutput->videoCodec
			VidiunLog::log("trying to fix H265 conversion");
			$gop = $flavorParamsOutput->gopSize; 					//gop_size	!!!$flavorParamsOutput->gopSize;
			$vBr = $flavorParamsOutput->videoBitrate; 				//video_bitrate	!!!$flavorParamsOutput->videoBitrate;
			$frameRate = $flavorParamsOutput->frameRate; 			//frame_rate	!!!$flavorParamsOutput->frameRate;
				
$threads = 4;
$pixFmt = "yuv420p";
			$cmdValsArr = explode(' ', $cmdStr);
			
			/*
			 * Rearrange the ffmpeg cmd-line into a complex pipe and multiple command
			 * - ffmpeg transcodes audio into an output.AAC file and decodes video into a raw resized video to be piped
			 * - into x265 that encodes raw output.h265
			 * - upon completion- mux into an out.mp4
			 * 
			 * To Do's
			 * - support other audio
			 * - support other formats
			 * 
			 */
			
				/*
				 * remove video codec
				 */
			if(in_array('-c:v', $cmdValsArr)) {
				$key = array_search('-c:v', $cmdValsArr);
				unset($cmdValsArr[$key+1]);
				unset($cmdValsArr[$key]);
			}
			if(in_array('-threads', $cmdValsArr)) {
				$key = array_search('-threads', $cmdValsArr);
				$threads = $cmdValsArr[$key+1];
			}
				/*
				 * add dual stream generation
				 */
			if(in_array('-c:a', $cmdValsArr)) {
				$key = array_search('-c:a', $cmdValsArr);
				$cmdValsArr[$key] = "-f yuv4mpegpipe -an - -vn -c:a";
			}
				/*
				 * handle pix-format (main vs main10)
				 */
			if(in_array('-pix_fmt', $cmdValsArr)) {
				$key = array_search('-pix_fmt', $cmdValsArr);
				$pixFmt = $cmdValsArr[$key+1];
			}
			switch($pixFmt){
				case "yuv420p10":
				case "yuv422p":
					$profile = "main10";
					break;
				case "yuv420p":
				default:
					$profile = "main";
					break;
			}

				/*
				 * Get source duration
				 */
			$ffParser = new VFFMpegMediaParser($srcFilePath);
			$ffMi = null;
			try {
				$ffMi = $ffParser->getMediaInfo();
			}
			catch(Exception $ex)
			{
				VidiunLog::log(print_r($ex,1));
			}
			if(isset($ffMi->containerDuration) && $ffMi->containerDuration>0)
				$duration = $ffMi->containerDuration/1000;
			else if(isset($ffMi->videoDuration) && $ffMi->videoDuration>0)
				$duration = $ffMi->videoDuration/1000;
			else if(isset($ffMi->audioDuration) && $ffMi->audioDuration>0)
				$duration = $ffMi->audioDuration/1000;
			else
				$duration = 0;
			
			$keyFramesArr = array();
			/*
			 * Generate x265 qpfile with forced key-frames
			 */
			if(isset($gop) && $gop>0 && isset($frameRate) && $frameRate>0 && isset($duration) && $duration>0){
				$gopInSec 	= $gop/round($frameRate);
				$frameDur 	= 1/$frameRate;
				for($kfTime=0,$kfId=0,$kfTimeGop=0;$kfTime<$duration; ){
					$keyFramesArr[] = $kfId;
					$kfId+=$gop;
					$kfTime=$kfId*$frameDur;
					$kfTimeGop+=$gopInSec;
					$kfTimeDelta = $kfTime-$kfTimeGop;
						/*
						 * Check for time derift conditions (for float fps, 29.97/23.947/etc) and fix when required
						 */
					if(abs($kfTimeDelta)>$frameDur){
						$aaa = $kfId;
						if($kfTimeDelta>0)
							$kfId--;
						else
							$kfId++;
					}
				}
				$keyFramesStr = implode(" I\n",$keyFramesArr)." I\n";
				file_put_contents("$outFilePath.qp", $keyFramesStr);
			}
			else {
				VidiunLog::log("Missing gop($gop) or frameRate($frameRate) or duration($duration) - will be generated without fixed keyframes!");
			}

			if(!in_array($outFilePath, $cmdValsArr)) {
				return $cmdStr;
			}
			
			$key = array_search($outFilePath, $cmdValsArr);
			$cmdValsArr[$key] = "$outFilePath.aac |"; 
			$cmdValsArr[$key].= " $x265bin - --profile $profile --y4m --scenecut 40 --min-keyint 1";
			if(isset($gop)) $cmdValsArr[$key].= " --keyint $gop";
			if(isset($vBr)) $cmdValsArr[$key].= " --bitrate $vBr";
			if(count($keyFramesArr)>0) $cmdValsArr[$key].= " --qpfile $outFilePath.qp";
			$cmdValsArr[$key].= " --threads $threads $outFilePath.h265";
			$cmdValsArr[$key].= " && $ffmpegExperimBin -i $outFilePath.aac -r $frameRate -i $outFilePath.h265 -c copy -f mp4 -y $outFilePath";
	
			$cmdStr = implode(" ", $cmdValsArr);
		}
		
		return $cmdStr;
	}
	
	/**
	 * 
	 * @param         $wmData
	 * @param string  $destFileSyncLocalPath
	 * @param string  $cmdLine
	 * @param string  $ffmpegBin
	 * @param string  $mediaInfoBin
	 * @return string
	 */
	public static function buildWatermarkedCommandLine($watermMarkData, $destFileSyncLocalPath, $cmdLine, $ffmpegBin = "ffmpeg", $mediaInfoBin = "mediainfo")
	{
		VidiunLog::log("In:cmdline($cmdLine)");
		if(!isset($mediaInfoBin) || strlen($mediaInfoBin)==0)
			$mediaInfoBin = "mediainfo";

		if(is_array($watermMarkData))
			$watermMarkDataArr = $watermMarkData;
		else
			$watermMarkDataArr = array($watermMarkData);
		$wmImgIdx = 1;
		foreach ($watermMarkDataArr as $wmData){
			VidiunLog::log("Watermark data($mediaInfoBin):\n".print_r($wmData,1));
				/*
			 	 * Retrieve watermark image file,
			 	 * either from image entry or from external url
			 	 * If both set, prefer image entry
			 	 */
			$imageDownloadUrl = null;
			$errStr = null;
			if(isset($wmData->imageEntry)){
				$version = null;
				try {
					$imgEntry = VBatchBase::$vClient->baseEntry->get($wmData->imageEntry, $version);
				}
				catch ( Exception $ex ) {
					$imgEntry = null;
					$errStr = "Exception on retrieval of an image entry($wmData->imageEntry),\nexception:".print_r($ex,1);
				}
				if(isset($imgEntry)){
					VidiunLog::log("Watermark entry: $wmData->imageEntry");
					$imageDownloadUrl = $imgEntry->downloadUrl;
				}
				else if(!isset($errStr)){
					$errStr = "Failed to retrieve an image entry($wmData->imageEntry)";
				}
				if(!isset($imgEntry))
					VidiunLog::notice($errStr);
			}
			
			if(!isset($imageDownloadUrl)){
				if(isset($wmData->url)) {
					$imageDownloadUrl = $wmData->url;
				}
				else {
					VidiunLog::notice("Missing watermark image data, neither via image-entry-id nor via external url.");
					return null;
				}
			}
			
			$wmTmpFilepath = $destFileSyncLocalPath."_$wmImgIdx.wmtmp";
			VidiunLog::log("imageDownloadUrl($imageDownloadUrl), wmTmpFilepath($wmTmpFilepath)");
				/*
				 * Get the watermark image file
				 */
			$curlWrapper = new VCurlWrapper();
			$res = $curlWrapper->exec($imageDownloadUrl, $wmTmpFilepath);
			VidiunLog::debug("Curl results: $res");
			if(!$res || $curlWrapper->getError()) {
				$errDescription = "Error: " . $curlWrapper->getError();
				$curlWrapper->close();
				VidiunLog::notice("Failed to curl the caption file url($imageDownloadUrl). Error ($errDescription)");
				return null;
			}
			$curlWrapper->close();
			
			if(!file_exists($wmTmpFilepath))
			{
				VidiunLog::notice("Error: output file ($wmTmpFilepath) doesn't exist");
				return null;
			}
			VidiunLog::log("Successfully retrieved the watermark image file ($wmTmpFilepath) ");
			
				/*
				 * Query the image file for format and dims
				 */
			$medPrsr = new VMediaInfoMediaParser($wmTmpFilepath, $mediaInfoBin);
			$imageMediaInfo=$medPrsr->getMediaInfo();
			if(!isset($imageMediaInfo)){
				VidiunLog::notice("Failed to retrieve media data from watermark file ($wmTmpFilepath). Carry on without watermark.");
				return null;
			}
			if(isset($imageMediaInfo->containerFormat)) $wmData->format = $imageMediaInfo->containerFormat;
			if(isset($imageMediaInfo->videoHeight)) $wmData->height = $imageMediaInfo->videoHeight;
			if(isset($imageMediaInfo->videoWidth))  {
				if(isset($wmData->fixImageDar) && $wmData->fixImageDar>0){
					$wmData->width = round($imageMediaInfo->videoWidth/$wmData->fixImageDar);
				}
				else {
					$wmData->width = $imageMediaInfo->videoWidth;
				}
			}
			
			if(strstr($wmData->format, "jpeg")!==false || strstr($wmData->format, "jpg")!==false) {
				$wmData->format = "jpg";
			}
			else if(strstr($wmData->format, "png")!==false){
				$wmData->format = "png";
			}
			rename($wmTmpFilepath, "$wmTmpFilepath.$wmData->format");
			$wmTmpFilepath = "$wmTmpFilepath.$wmData->format";

			VidiunLog::log("Updated Watermark data:".json_encode($wmData));

			$cmdLine = VDLOperatorFfmpeg::AdjustCmdlineWithWatermarkData($cmdLine, $wmData, $wmTmpFilepath, $wmImgIdx);
			$wmImgIdx++;
		}
		return $cmdLine;
	}
	
	/**
	 * 
	 */
	protected static function buildNGSPairedDigitalWatermarkingCommandLine($cmdLine, $data)
	{
			/*
			 * Get source mediainfo for NGS prepprocessor params
			 * Use default 'NGS_FragmentPreprocessorYUV' if batch config does not contain 'ngsPreprocessorCmd'
			 */
		if(count($data->srcFileSyncs)>0 && isset($data->srcFileSyncs[0]->assetId)) { 
			$mediaInfoFilter = new VidiunMediaInfoFilter();
			$mediaInfoFilter->flavorAssetIdEqual = $data->srcFileSyncs[0]->assetId;
			try {
				$mediaInfoList = VBatchBase::$vClient->mediaInfo->listAction($mediaInfoFilter);
			}
			catch (Exception $ex) {
				$mediaInfoList = null;
				$errStr = "Exception on retrieval of an mediaInfo List ($mediaInfoFilter->flavorAssetIdEqual),\nexception:".print_r($ex,1);
			}

			if(!(isset($mediaInfoList) && isset($mediaInfoList->objects) && count($mediaInfoList->objects)>0)){
				if(!isset($errStr))
					$errStr = "Bad source media info object";
				VidiunLog::notice($errStr);
				return null;
			}
			
			$mediaInfo = $mediaInfoList->objects[0];
			$ngsBin = isset(VBatchBase::$taskConfig->params->ngsPreprocessorCmd)? VBatchBase::$taskConfig->params->ngsPreprocessorCmd: "NGS_FragmentPreprocessorYUV";
			$srcWid = $mediaInfo->videoWidth;
			$srcHgt = $mediaInfo->videoHeight;
			$srcFps = $mediaInfo->videoFrameRate;
			if(strstr($data->flavorParamsOutput->tags,VConversionEngineFfmpeg::TAG_VARIANT_A)!=false) 
				$prepMode='A';
			else if(strstr($data->flavorParamsOutput->tags,VConversionEngineFfmpeg::TAG_VARIANT_B)!=false)
				$prepMode='APrime';
			else
				return null;
		}

$stub=null;
		if(strstr($data->flavorParamsOutput->tags,VConversionEngineFfmpeg::TAG_NGS_STUB)!=false)
			$stub="--stub";
	
		$digSignStub = "-f rawvideo -s %dx%d -pix_fmt yuv420p - | %s -w %d -h %d -f %s %s --%s| %s -f rawvideo -s %dx%d -r %s -i -";
		$digSignStr = sprintf($digSignStub, $srcWid, $srcHgt, $ngsBin, $srcWid, $srcHgt, $srcFps, 
				      		$stub, $prepMode,VDLCmdlinePlaceholders::BinaryName, $srcWid, $srcHgt, $srcFps);

		$cmdLine = VDLOperatorFfmpeg::SplitCommandLineForVideoPiping($cmdLine, $digSignStr);
		VidiunLog::log("After:cmdLine($cmdLine)");
		return $cmdLine;
	}

	/**
	 */
	public static function buildSubtitlesCommandLine($subtitlesData, $data, $cmdLine, &$jobMsg)
	{
		/*
		 * Currently the precessing of action 'render'(burn-in) and 'embed'
		 * is the same.
		 * It will be separated in the future to support embedding of multiple subs languages 
		 * (this option is irrelevant for rendering)
		 */
		VidiunLog::log("subtitlesData:".json_encode($subtitlesData));
		$captionsArr = self::fetchEntryCaptionList($data, $jobMsg);
		if(!isset($captionsArr) || count($captionsArr)==0){
			VidiunLog::log("No captions for that entry!!!");
			$cmdLine=VDLOperatorFfmpeg::RemoveFilter($cmdLine, 'subtitles');
			return $cmdLine;
		}
		$captionFilePath = null;
		foreach($captionsArr as $lang=>$captionFileUrl){
			if($subtitlesData->language==$lang){
				VidiunLog::log("Found required language($lang)");
				$captionFilePath = self::fetchCaptionFile($captionFileUrl, $data->destFileSyncLocalPath.".temp.$lang.srt");
				break;
			}
		}
		if(!isset($captionFilePath)){
			VidiunLog::notice("No captions for ($subtitlesData->language)");
			$cmdLine=VDLOperatorFfmpeg::RemoveFilter($cmdLine, 'subtitles');
			return $cmdLine;
		}
		$cmdLine = str_replace(VDLCmdlinePlaceholders::SubTitlesFileName, $captionFilePath, $cmdLine);

		VidiunLog::log($cmdLine);
		return $cmdLine;
	}

	/**
	 */
	public static function fetchEntryCaptionList($data, $jobMsg)
	{
		VidiunLog::log("asset:$data->flavorAssetId");
		try {
			VBatchBase::$vClient->getConfig()->partnerId = $data->flavorParamsOutput->partnerId;
			
			$flavorAsset = VBatchBase::$vClient->flavorAsset->get($data->flavorAssetId);
			if(!isset($flavorAsset)){
				$jobMsg = "Failed to retrieve the flavor asset object (".$data->flavorAssetId.")";
				VidiunLog::notice("ERROR:".$jobMsg);
				return null;
			}
		VidiunLog::log("entry:$flavorAsset->entryId");
			$filter = new VidiunAssetFilter();
			$filter->entryIdEqual = $flavorAsset->entryId;
			$captionAssetList = VBatchBase::$vClient->captionAsset->listAction($filter, null); 
			if(!isset($captionAssetList) || !$captionAssetList->objects || count($captionAssetList->objects)==0){
				$jobMsg = "No caption assets for entry (".$flavorAsset->entryId.")";
				VidiunLog::notice("ERROR:".$jobMsg);
				return null;
			}
		}
		catch( Exception $ex){
			$jobMsg = "Exception on captions list retrieval  (".print_r($ex,1).")";
			VidiunLog::notice("ERROR:".$jobMsg);
			return null;
		}
		
		VidiunLog::log("Fetching captions (#".count($captionAssetList->objects).")");
		$captionsArr = array();
		foreach($captionAssetList->objects as $captionObj) {
			try{
				$captionsUrl = VBatchBase::$vClient->captionAsset->getUrl($captionObj->id, null);
			}
			catch ( Exception $ex ) {
				$captionsUrl = null;
				VidiunLog::notice("Exception on retrieve caption asset url retrieval (".$captionObj->id."),\nexception:".print_r($ex,1));
			}		
			if(!isset($captionsUrl)){
				VidiunLog::notice("Failed to retrieve caption asset url (".$captionObj->id.")");
				continue;
			}
			$captionsArr[$captionObj->languageCode] = $captionsUrl;
			VidiunLog::log("Caption - lang($captionObj->languageCode), url($captionsUrl)");
		}
		
		if(count($captionsArr)==0) {
			$jobMsg = "No captions for that entry ($flavorAsset->entryId)!!!";
			VidiunLog::log($jobMsg);
			return null;
		}
		
		VidiunLog::log("Fetched:".serialize($captionsArr));
		return $captionsArr;
	}

	/***************************
	 * fetchCaptionFile
	 *
	 * @param $languageCode
	 * @param $destFolder
	 * @return $localCaptionFilePath
	 */
	public static function fetchCaptionFile($captionUrl, $captionFilePath)
	{
		VidiunLog::log("Executing curl to retrieve caption asset file from - $captionUrl");
		$curlWrapper = new VCurlWrapper();
		VidiunLog::log("captionFilePath:$captionFilePath");
		$res = $curlWrapper->exec($captionUrl, $captionFilePath, null, true);
		VidiunLog::log("Curl results: $res");
		if(!$res || $curlWrapper->getError()){
			$errDescription = "Error: " . $curlWrapper->getError();
			$curlWrapper->close();
			VidiunLog::notice("Failed to curl the caption file url($captionUrl). Error ($errDescription)");
			return null;
		}
		$curlWrapper->close();
		
		if(!file_exists($captionFilePath)) {
			VidiunLog::notice("Error: output file ($captionFilePath) doesn't exist");
			return null;
		}
		VidiunLog::log("Successfully retrieved $captionFilePath!");
		return $captionFilePath;
	}
}


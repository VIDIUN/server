<?php
/**
 * @package plugins.ffmpeg
 * @subpackage lib
 */
class VDLOperatorFfmpeg extends VDLOperatorBase {
/*
    public function __construct($id, $name=null, $sourceBlacklist=null, $targetBlacklist=null) {
    	parent::__construct($id, $name, $sourceBlacklist,$targetBlacklist);
    }
*/
	/* ---------------------------
	 * GenerateCommandLine
	 */
    public function GenerateCommandLine(VDLFlavor $design, VDLFlavor $target, $extra=null)
	{
// rem ffmpeg -i <infilename> -vcodec flv   -r 25 -b 500k  -ar 22050 -ac 2 -acodec libmp3lame -f flv -t 60 -y <outfilename>

		$cmdStr = " -i ".VDLCmdlinePlaceholders::InFileName;
		
		$cmdStr.= $this->generateVideoParams($design, $target);
		$cmdStr.= $this->generateAudioParams($design, $target);
		$cmdStr.= $this->generateContainerParams($design, $target);

		$cmdStr = $this->processClipping($target, $cmdStr);
		
		if($extra)
			$cmdStr.= " ".$extra;
		
		$cmdStr.= " -y ".VDLCmdlinePlaceholders::OutFileName;

		return $cmdStr;
	}
	
	/* ---------------------------
	 * generateVideoParams
	 */
    protected function generateVideoParams(VDLFlavor $design, VDLFlavor $target)
	{
		if(!isset($target->_video)){
			return " -vn";
		}
		
		$vcodecParams = $this->getVideoCodecSpecificParams($design, $target);
		if(!isset($vcodecParams))
			return null;
		
$vid = $target->_video;
$vidBr = $vid->_bitRate;
		switch($vid->_id){
		case VDLVideoTarget::VP6:
			if(isset($design->_video) && $design->_video->_bitRate){
				$vidBr=$this->fixVP6BitRate($design->_video->_bitRate, $vid->_bitRate);
			}
			break;
		}
		
$cmdStr = null;
		$cmdStr = $cmdStr." -vcodec ".$vcodecParams;
		
		if($vidBr){
			$cmdStr .= " -b ".$vidBr."k";
		}
$bt=0;
		if(isset($vid->_cbr) && $vid->_cbr>0) {
			$bt = round($vidBr/10);
			$cmdStr.= " -minrate ".$vidBr."k";
			$cmdStr.= " -maxrate ".$vidBr."k";
			$cmdStr.= " -bufsize ".round($vidBr/5)."k";
		}
		if(isset($vid->_bt) && $vid->_bt>0) {
			$cmdStr.= " -bt ".$vid->_bt."k";
		}
		else if($bt>0){
			$cmdStr.= " -bt $bt"."k";
		}
		
		/*
		 * DV video should get 'target' operand, rather than frame size.
		 */
		if($vid->_id==VDLVideoTarget::DV) {
			if(isset($vid->_height)) {
				switch($vid->_height) {
				case 480: 
					$cmdStr.= " -target ntsc-dv";
					break;
				case 576: 
					$cmdStr.= " -target pal-dv";
					break;
				}
			}
		}
		else if($vid->_width!=null && $vid->_height!=null){
			$cmdStr.= " -s ".$vid->_width."x".$vid->_height;
		}
		
		if(isset($vid->_dar) && $vid->_dar>0) {
			$cmdStr.= " -aspect ".round($vid->_dar,4);
		}
		if($vid->_frameRate!==null && $vid->_frameRate>0){
			$cmdStr .= " -r ".$vid->_frameRate;
		}
		if($vid->_gop!==null && $vid->_gop>0){
			$cmdStr .= " -g ".$vid->_gop;
		}
		if($vid->_scanType!==null && $vid->_scanType>0){ // ScanType 0:progressive, 1:interlaced
			$cmdStr .= " -deinterlace";
		}

		return $cmdStr;
	}
	
	/* ---------------------------
	 * getVideoCodecName
	 */
    protected function getVideoCodecSpecificParams(VDLFlavor $design, VDLFlavor $target)
	{
$vidObj = $target->_video;
		switch($vidObj->_id){
		case VDLVideoTarget::VP6:
		case VDLVideoTarget::FLV:
		case VDLVideoTarget::H263:
			return "flv";
		case VDLVideoTarget::H264:
		case VDLVideoTarget::H264B:
		case VDLVideoTarget::H264M:
		case VDLVideoTarget::H264H:
			return "libx264 ".$this->generateH264params($vidObj);
		case VDLVideoTarget::MPEG4:
			return "mpeg4";
		case VDLVideoTarget::THEORA:
			return "libtheora";
		case VDLVideoTarget::WMV2:
		case VDLVideoTarget::WMV3:
		case VDLVideoTarget::WVC1A:
			return "wmv2";
		case VDLVideoTarget::VP8:
			return "libvpx";
		case VDLVideoTarget::MPEG2:
			return "mpeg2video";
		case VDLVideoTarget::DV:
			return "dvvideo";
		case VDLVideoTarget::COPY:
			return "copy";
		default:
			return null;
		}
	}
	
	/* ---------------------------
	 * generateAudioParams
	 */
    protected function generateAudioParams(VDLFlavor $design, VDLFlavor $target)
	{
		if(!isset($target->_audio)) {
			return " -an";
		}
		
$acodec = "libmp3lam";
$cmdStr = null;
$aud = $target->_audio;
		switch($aud->_id){
			case VDLAudioTarget::MP3:
				$acodec = "libmp3lame";
				break;
			case VDLAudioTarget::AAC:
				$acodec = "libfaac";
				break;
			case VDLAudioTarget::VORBIS:
				$acodec = "libvorbis";
				break;
			case VDLAudioTarget::WMA:
				$acodec = "wmav2";
				break;
			case VDLAudioTarget::AMRNB:
				// common settings - -ab 12.2k -ar 8000 -ac 1
				$acodec = "libopencore_amrnb";
				break;
			case VDLAudioTarget::MPEG2:
				$acodec = "mp2";
				break;
			case VDLAudioTarget::PCM:
				if(isset($aud->_resolution) && in_array($aud->_resolution, array(16,24,32))) {
					$acodec = "pcm_s".$aud->_resolution."le";
				}
				else {
					$acodec = "pcm_s16le";
				}
				break;
			case VDLAudioTarget::COPY:
				$acodec = "copy";
				break;
		}
		
		$cmdStr.= " -acodec ".$acodec;
		if($aud->_bitRate!==null && $aud->_bitRate>0){
			$cmdStr.= " -ab ".$aud->_bitRate."k";
		}
		if($aud->_sampleRate!==null && $aud->_sampleRate>0){
			$cmdStr.= " -ar ".$aud->_sampleRate;
		}
		if($aud->_channels!==null && $aud->_channels>0){
			$cmdStr.= " -ac ".$aud->_channels;
		}

		return $cmdStr;
	}

	/* ---------------------------
	 * generateContainerParams
	 */
    protected function generateContainerParams(VDLFlavor $design, VDLFlavor $target)
	{
		if(!isset($target->_container)) 
			return null;

$format = "fl";
$cmdStr = null;
$con = $target->_container;
		switch($con->_id){
			case VDLContainerTarget::FLV:
				$format = "flv";
				break;
			case VDLContainerTarget::AVI:
			case VDLContainerTarget::MP4:
			case VDLContainerTarget::_3GP:
			case VDLContainerTarget::MOV:
			case VDLContainerTarget::MP3:
			case VDLContainerTarget::OGG:
			case VDLContainerTarget::M4V:
			case VDLContainerTarget::MXF:
				$format = $con->_id;
				break;
			case VDLContainerTarget::OGV:
				$format = "ogg";
				break;
			case VDLContainerTarget::WMV:
				$format = "asf";
				break;
			case VDLContainerTarget::MKV:
				$format = "matroska";
				break;
			case VDLContainerTarget::WEBM:
				$format = "webm";
				break;
			case VDLContainerTarget::MPEGTS:
			case VDLContainerTarget::M2TS:
			case VDLContainerTarget::APPLEHTTP:
				$format = "mpegts";
				break;
			case VDLContainerTarget::MPEG:
				$format = "mpeg";
				break;
			case VDLContainerTarget::WAV:
				$format = "wav";
				break;
		}
		$cmdStr.= " -f ".$format;

		return $cmdStr;
	}
	
	/* ---------------------------
	 * processClipping 
	 */
	protected function processClipping(VDLFlavor $target, $cmdStr)
	{
		$clipStr=null;
		if(isset($target->_clipStart) && $target->_clipStart>0){
			$clipStr.= " -ss ".$target->_clipStart/1000;
		}
		if(isset($target->_clipDur) && $target->_clipDur>0){
			$clipStr.= " -t ".$target->_clipDur/1000;
		}
		if(!isset($clipStr))
			return $cmdStr;
		/*
		 * Following 'dummy' seek-to setting is done to ensure preciseness
		 * of the main seek command that is done at the beginning of the command line
		 */
		if($target->_fastSeekTo==true){
			$cmdStr = $clipStr.$cmdStr;
			$cmdStr.= " -ss 0.01";
		}
		else {
			$cmdStr.= $clipStr;
		}
		
		return $cmdStr;
	}
	
	/* ---------------------------
	 * generateH264params
	 */
	protected function generateH264params($videoObject)
	{
/*
-sws - scale quality option, 0-lowest, 9- good
-subq - sub-pixel and partion search algorithms. 1 - fast/low quality, 9-slow/best quality, 5-average
global_header - makes it baseline
b_pyramid - to be used with bframes>2 (main and high profile)

good mencoder32 ~/Media/Canon.Rotated.0_qaqsufbl.avi -o ~/Media/aaa.mp4 -of lavf -lavfopts format=mp4 -ofps 25 -ovc x264 -sws 9 -x264encopts bitrate=300:subq=5:frameref=6:bframes=3:b_pyramid=1:weight_b=1:threads=auto:keyint=60:level_idc=30:global_header:partitions=all:trellis=1:chroma_me:me=umh:8x8dct -vf scale=304:176 -oac faac -faacopts mpeg=4:object=2:br=96 ; mediainfo ~/Media/aaa.mp4
bad  mencoder32 ~/Media/Canon.Rotated.0_qaqsufbl.avi -of lavf -lavfopts format=mp4 -ofps 25 -ovc x264        -x264encopts bitrate=300:subq=7:frameref=6:bframes=3:b_pyramid=1:weight_b=1:threads=auto:level_idc=30:global_header:8x8dct:trellis=1:chroma_me:me=umh:keyint=60 -vf harddup,scale=304:176 -oac faac -faacopts mpeg=4:object=2:br=96 -endpos 10 -o ~/Media/aaa.mp4; mediainfo ~/Media/aaa.mp4
     mencoder32 ~/Media/Canon.Rotated.0_qaqsufbl.avi -of lavf -lavfopts format=mp4 -ofps 25 -ovc x264 -sws 9 -x264encopts bitrate=300:subq=5:frameref=6:bframes=3:b_pyramid=1:weight_b=1:threads=auto:level_idc=30:global_header:partitions=all:trellis=1:chroma_me:me=umh:8x8dct:keyint=60 -vf scale=304:176 -oac faac -faacopts mpeg=4:object=2:br=96 -o ~/Media/aaa.mp4; mediainfo ~/Media/aaa.mp4
 
 */
		$h264params=null;
		$ffQsettings = " -qcomp 0.6 -qmin 10 -qmax 50 -qdiff 4";
		switch($videoObject->_id) {
		case VDLVideoTarget::H264:
			$h264params=" -subq 2".$ffQsettings;
			if($videoObject->_bitRate<VDLConstants::LowBitrateThresHold) {
				$h264params .= " -crf 30";
			}
			break;
		case VDLVideoTarget::H264B:
			$h264params=" -subq 2".$ffQsettings." -coder 0";;
			if($videoObject->_bitRate<VDLConstants::LowBitrateThresHold) {
				$h264params .= " -crf 30";
			}
			break;
		case VDLVideoTarget::H264M:
			$h264params=" -subq 5".$ffQsettings." -coder 1 -refs 2";
			if($videoObject->_bitRate<VDLConstants::LowBitrateThresHold) {
				$h264params .= " -crf 30";
			}
			break;
		case VDLVideoTarget::H264H:				
			$h264params=" -subq 7".$ffQsettings." -bf 16 -coder 1 -refs 6 -flags2 +bpyramid+wpred+mixed_refs+dct8x8+fastpskip";
			if($videoObject->_bitRate<VDLConstants::LowBitrateThresHold) {
				$h264params .= " -crf 30";
			}
			break;
		}
		return $h264params;
	}
	
	/* ---------------------------
	 * CheckConstraints
	 */
	public function CheckConstraints(VDLMediaDataSet $source, VDLFlavor $target, array &$errors=null, array &$warnings=null)
	{
	    if(parent::CheckConstraints($source, $target, $errors, $warnings)==true)
			return true;

		/*
		 * Remove ffmpegs
		 * for rotated videos
		 */
		if($target->_video && $target->_video->_rotation) {
			$warnings[VDLConstants::VideoIndex][] = //"The transcoder (".$key.") does not handle properly DAR<>PAR.";
				VDLWarnings::ToString(VDLWarnings::TranscoderLimitation, $this->_id);
			return true;
		}

		return $this->checkBasicFFmpegConstraints($source, $target, $errors, $warnings);
	}

	/* ---------------------------
	 * checkBasicFFmpegConstraints
	 */
	protected function checkBasicFFmpegConstraints(VDLMediaDataSet $source, VDLFlavor $target, array &$errors=null, array &$warnings=null)
	{
		/*
		 * Non Mac transcoders should not mess up with QT/WMV/WMA
		 * 
		 */
		$qt_wmv_list = array("wmv1","wmv2","wmv3","wvc1","wmva","wma1","wma2","wmapro");
		if($source->_container && ($source->_container->_id=="qt" || $source->_container->_format=="qt")
		&& (
			($source->_video && (in_array($source->_video->_format,$qt_wmv_list)||in_array($source->_video->_id,$qt_wmv_list)))
			||($source->_audio && (in_array($source->_audio->_format,$qt_wmv_list)||in_array($source->_audio->_id,$qt_wmv_list)))
			)
		){
			$warnings[VDLConstants::VideoIndex][] = //"The transcoder (".$key.") can not process the (".$sourcePart->_id."/".$sourcePart->_format. ").";
				VDLWarnings::ToString(VDLWarnings::TranscoderFormat, $this->_id, "qt/wmv/wma");
			return true;
		}
		
		return false;
	}

	/* ---------------------------
	 * SplitArgumentsString
	 */
	public static function SplitArgumentsString($argsStr)
	{
		$paramsArr = explode(" ",$argsStr);
		$params = array();
		$prev = null;
		foreach($paramsArr as $prm){
			$prm = trim($prm);
			if(strlen($prm)==0)
				continue;
			if($prm[0]=='-'){
				$params[$prm]=null;
				$prev=$prm;
			}
			else {
				$params[$prev]=$prm;
			}
		}
		return $params;
	}
	
	/**
	 * 
	 */
	public static function AdjustCmdlineWithWatermarkData($cmdLine, $wmData, $wmFilePath, $wmImgIdx)
	{
		VidiunLog::log("cmdLine($cmdLine),wmFilePath($wmFilePath), wmImgIdx($wmImgIdx),wmData:".json_encode($wmData));
			/*
			 * evaluate WM scale and margins, if any
			 */
		if(isset($wmData->scale) && is_string($wmData->scale)) {
			$wmData->scale = explode("x",$wmData->scale);
		}
		if(isset($wmData->margins) && is_string($wmData->margins)) {
			$wmData->margins = explode("x",$wmData->margins);
		}
	
		VidiunLog::log("Updated Watermark data:\n".print_r($wmData,1));

			/*
			 * Evaluate WM scaling params and scale it accordingly
			 */
		$wid=null; $hgt=null;
		if(!isset($wmData->scale) && ($wmData->width%2!=0 || $wmData->height%2!=0)){
			$wmData->scale = array();
			$wmData->width -= $wmData->width%2;
			$wmData->height -= $wmData->height%2;
			$wmData->scale[0] = $wmData->width;
			$wmData->scale[1] = $wmData->height;
		}
		if(isset($wmData->scale)){
			$wid = in_array($wmData->scale[0],array(null,0,-1))? -1: $wmData->scale[0];
			$hgt = in_array($wmData->scale[1],array(null,0,-1))? -1: $wmData->scale[1];
			if($wid<=0) {
				$wid = round($hgt*$wmData->width/$wmData->height);
			}
			if($hgt<=0) {
				$hgt = round($wid*$wmData->height/$wmData->width);
			}
			if(!($wid>0 && $hgt>0)) {
				$wid = $wmData->width; $hgt = $wmData->height;
			}
			if($wid>0) $wid -= $wid%2;
			if($hgt>0) $hgt -= $hgt%2;

		}
		else{
			$wid = $wmData->width; $hgt = $wmData->height;
		}
/* Samples - 
"[1]scale=100:100,setsar=100/100[logo];[0:v]crop=100:100:iw-ow-10:300,setsar=100/100[cropped];[cropped][logo]blend=all_expr='if(eq(mod(X,2),mod(Y,2)),A,B)'[blended];[0:v][blended]overlay=main_w-overlay_w-10:300[out]"
"[1]scale=100:100,setsar=100/100[logo];[0:v][logo]overlay=main_w-overlay_w-10:300[out]" -map "[out]"
*/
		$cmdLine = str_replace(
				array(VDLCmdlinePlaceholders::WaterMarkFileName."_$wmImgIdx",VDLCmdlinePlaceholders::WaterMarkWidth."_$wmImgIdx",VDLCmdlinePlaceholders::WaterMarkHeight."_$wmImgIdx"), 
				array($wmFilePath, $wid, $hgt),
				$cmdLine);
		VidiunLog::log("After:cmdline($cmdLine)");
		return $cmdLine;
	}
	
	/**
	 * 
	 */
	public static function RemoveFilter($cmdLine, $filterName)
	{
		$cmdValsArr = explode(' ', $cmdLine);
			
		$keys=array_keys($cmdValsArr, "-filter_complex");
		if(!isset($keys) || count($keys)==0)
			return $cmdLine;
		
		$toRemove= null;
		foreach ($keys as $key)
		{
			if(!array_key_exists($key+1,$cmdValsArr))
				continue;
			$toRemove=strstr($cmdValsArr[$key+1], $filterName);
			if($toRemove!=false)
				break;
		}
		if(!isset($toRemove) || $toRemove==false)
			return $cmdLine;
		
		$filtersArr = explode(';', $cmdValsArr[$key+1]);
		if(count($filtersArr)==1){
			unset($cmdValsArr[$key+1]);
			unset($cmdValsArr[$key]);
			$cmdLine = implode(' ', $cmdValsArr);
			return $cmdLine;
		}
		foreach($filtersArr as $vFlt=>$filter){
			$toRemove=strstr($filter, $filterName);
			if($toRemove!=false)
				break;
		}
		
		$pipeName = str_replace($toRemove, '', $filter);
		unset($filtersArr[$key+1]);
		unset($filtersArr[$vFlt]);
		$lastChar = substr($toRemove,-1);
		if($lastChar!='\'' && $lastChar!='\"')
			$lastChar = null;
		$filtersArr[$vFlt-1] = str_replace($pipeName, $lastChar, $filtersArr[$vFlt-1]);
		
		$cmdValsArr[$key+1] = implode(';', $filtersArr);
		$cmdLine = implode(' ', $cmdValsArr);
		return $cmdLine;
	}

	/**
	 * 
	 * @param unknown_type $execCmd
	 * @return unknown|mixed
	 */
	public static function ExpandForcedKeyframesParams($execCmd)
	{
		$cmdLineWithKeyframes = strstr($execCmd, VDLCmdlinePlaceholders::ForceKeyframes);
		if($cmdLineWithKeyframes==false){
			return $execCmd;
		}
		
		$cmdLineWithKeyframes = explode(" ",$cmdLineWithKeyframes);	// 
		$cmdLineWithKeyframes = $cmdLineWithKeyframes[0];
		$kfPrms = substr($cmdLineWithKeyframes,strlen(VDLCmdlinePlaceholders::ForceKeyframes));
		$kfPrms = explode("_",$kfPrms);
		$forcedKF=null;
		for($t=0,$tr=0;$t<=$kfPrms[0]; $t+=$kfPrms[1], $tr+=round($kfPrms[1])){
			// The check bellow is to prevent 'dripping' of the kf timing
			if($tr && round($t)>$tr) {
				$t=$tr;
			}
			$forcedKF.=",".round($t,4);
		}
		$forcedKF[0] = ' ';
		$execCmd = str_replace ( 
				array($cmdLineWithKeyframes), 
				array($forcedKF),
				$execCmd);
		return $execCmd;
	}

	/**
	 * @method
	 * @param unknown_type $cmdLine
	 * @param unknown_type $pipeStr
	 * @return mixed
	 */
	public static function SplitCommandLineForVideoPiping($cmdLine, $pipeStr)
	{
		VidiunLog::log("Before:cmdLine($cmdLine)");
		$cmdLineArr = explode(' ', $cmdLine);
	
		$ffmpegBin = $cmdLineArr[0];
	
	
		// Check for '2pass' - 2-pass is compound cmd-line that contain contains twice the source op (one per pass)
		// Get the source file
		$vArr = array_keys($cmdLineArr,'-i');
		$is2pass=(count($vArr)>1);
		if($is2pass){
			$keySrc = $vArr[1]+1;
		}
		else {
			$keySrc = $vArr[0]+1;
		}
		$srcFile = $cmdLineArr[$keySrc];
	
		$vArr = array_keys($cmdLineArr,'-an');
		if(count($vArr)==0 || ($is2pass && count($vArr)==1)) {
			/*
			 * Fix the audio source mapping, to adjust for separating video and audio sources
			 * in order to support NGS (video only) piping.
			 * Audio source mapping changed to 1 (original 0)
			 * For 2-pass sessions - the 1st pass should remain as-is
			 * and this fix should be applied only to the 2nd pass
			 */
			$vArr = array_keys($cmdLineArr,'-filter_complex');
			if(count($vArr)>0) {
				$vFilterIdx = end($vArr);
				$filterStr = $cmdLineArr[$vFilterIdx+1];
				$filterArr = explode(';', $filterStr);
				foreach($filterArr as $idx=>$filterStr){
					/*
					 * Only audio filters should be fixed (pan,amix,amerge)
					*/
					if(preg_match("/\b(pan|amix|amerge)\b/", $filterStr)==1)
						$filterArr[$idx] = str_replace ('[0:','[1:',$filterStr);
				}
				$cmdLineArr[$vFilterIdx+1] = implode(';', $filterArr);
			}
			$vArr = array_keys($cmdLineArr,'-map');
			if(count($vArr)>0) {
				foreach ($vArr as $vIdx){
					/*
					 * The mapping fix should be applied only to the 2nd pass
					 */
					if($is2pass && $vIdx<=$keySrc){
						continue;
					}
					$mapStr = $cmdLineArr[$vIdx+1];
					if($mapStr=='v'){
						$cmdLineArr[$vIdx+1] = "0:v";
					}
					else if(strncmp($mapStr,"0:",2)==0 || $mapStr[2]!='a'){
						$cmdLineArr[$vIdx+1] = str_replace ('0:','1:',$mapStr);
					}
				}
				$pipeStr.= " -i $srcFile";
			}
			else
				$pipeStr.= " -i $srcFile -map 0:v -map 1:a";
		}
		VidiunLog::log("Fixed part:$pipeStr");
		$cmdLineArr[$keySrc].= " $pipeStr";
		$cmdLine = implode(" ", $cmdLineArr);
		$cmdLine = str_replace (VDLCmdlinePlaceholders::BinaryName,$ffmpegBin,$cmdLine);
		VidiunLog::log("After:cmdLine($cmdLine)");
		return $cmdLine;
	}
}
	

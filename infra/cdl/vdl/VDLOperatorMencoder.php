<?php
/**
 * @package plugins.mencoder
 * @subpackage lib
 */
class VDLOperatorMencoder extends VDLOperatorBase {
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
	$cmdStr = null;

		$cmdStr.= " ".VDLCmdlinePlaceholders::InFileName;

		$cmdStr.= $this->generateContainerParams($design, $target);
		$cmdStr.= $this->generateVideoParams($design, $target);
		$cmdStr.= $this->generateAudioParams($design, $target);

		$clipStart=0;
		if($target->_clipStart!==null && $target->_clipStart>0){
			$clipStart = $target->_clipStart;
			$cmdStr.= " -ss ".$clipStart/1000;
		}
		
		if($target->_clipDur!==null && $target->_clipDur>0){
			$cmdStr.= " -endpos ".($clipStart+$target->_clipDur)/1000;
		}
		
		if($extra)
			$cmdStr.= " ".$extra;
		
		$cmdStr.= " -o ".VDLCmdlinePlaceholders::OutFileName;
		
		return $cmdStr;
	}
	
	/* ---------------------------
	 * generateVideoParams
	 */
    protected function generateVideoParams(VDLFlavor $design, VDLFlavor $target)
	{
		if(!isset($target->_video)){
			return " -novideo";
		}

$vcodecParams = "fl";
$cmdStr = null;
$vid = $target->_video;
$vbrFixedVp6=null;
		if($vid->_frameRate)
			$cmdStr.= " -ofps ".$vid->_frameRate;
		else
			$cmdStr.= " -ofps ".VDLConstants::MaxFramerate;
		
		switch($vid->_id){
			case VDLVideoTarget::VP6:
				if(isset($design->_video) && $design->_video->_bitRate)
					$vbrFixedVp6=$this->fixVP6BitRate($design->_video->_bitRate, $vid->_bitRate);
			case VDLVideoTarget::FLV:
			case VDLVideoTarget::H263:
				$cmdStr.= " -ovc lavc";
				$cmdStr.= " -lavcopts vcodec=flv";
$vBr = isset($vbrFixedVp6)? $vbrFixedVp6: $vid->_bitRate;
				if($vBr) {
					$cmdStr.= ":vbitrate=".$vBr;
				}
				$cmdStr.= ":mbd=2:mv0:trell:v4mv:cbp:last_pred=3";
//					$cmdStr.= ":mbd=2:mv0:trell:v4mv:cbp";
				break;
			case VDLVideoTarget::H264:
			case VDLVideoTarget::H264B:
			case VDLVideoTarget::H264M:
			case VDLVideoTarget::H264H:
				$cmdStr.= $this->generateH264params($vid);
				break; 				
			case VDLVideoTarget::MPEG4:
				$cmdStr.= " -ovc lavc";
				$cmdStr.= " -lavcopts vcodec=mpeg4";
				if($vid->_bitRate) {
					$cmdStr.= ":vbitrate=".$vid->_bitRate;
				}
				break;
			case VDLVideoTarget::WMV2:
			case VDLVideoTarget::WMV3:
			case VDLVideoTarget::WVC1A:
				$cmdStr.= " -ovc lavc";
				$cmdStr.= " -lavcopts vcodec=wmv2";
				if($vid->_bitRate) {
					$cmdStr.= ":vbitrate=".$vid->_bitRate;
				}
				break;
		}

		if($vid->_gop!==null && $vid->_gop>0)
			$cmdStr.= ":keyint=".$vid->_gop;
		$cmdStr.= " -vf harddup";
		if($vid->_width && $vid->_height)
			$cmdStr.= ",scale=".$vid->_width.":".$vid->_height;
		if($vid->_scanType!==null && $vid->_scanType>0) // ScanType 0:progressive, 1:interlaced
			$cmdStr.= ",yadif=3,mcdeint,framestep=2";
		if(isset($vid->_rotation)) {
			if($vid->_rotation==180)
				$cmdStr.= ",flip";
			else if($vid->_rotation==90)
				$cmdStr.= ",rotate=1";
			else if($vid->_rotation==270 || $vid->_rotation==-90)
				$cmdStr.= ",rotate=3";
		}
		return $cmdStr;
	}
	
	/* ---------------------------
	 * generateAudioParams
	 */
    protected function generateAudioParams(VDLFlavor $design, VDLFlavor $target)
	{
		if(!isset($target->_audio)) {
			return " -nosound";
		}
		
$acodec = "libmp3lam";
$cmdStr = null;
$aud = $target->_audio;

		if($aud->_id==VDLAudioTarget::MP3){
			$cmdStr.= " -oac mp3lame -lameopts abr";
			if($aud->_bitRate)
				$cmdStr.= ":br=".$aud->_bitRate;
//				if($aud->_channels)
//					$cmdStr.= " -ac ".$aud->_channels;
		}
///web/vidiun/bin/x64/mencoder -endpos 2 $1 -of lavf -lavfopts format=avi -ovc x264 -ofps 25 -x264encopts bitrate=500 -vf scale=1280:720,harddup -oac faac -srate 48000 -channels 5 -faacopts mpeg=4:object=2:br=32 -o $outputFile
		else if($aud->_id==VDLAudioTarget::AAC){
			$cmdStr.= " -oac faac -faacopts mpeg=4:object=2:tns:raw";
			if($aud->_bitRate)
				$cmdStr.= ":br=".$aud->_bitRate;
		}
		else if($aud->_id==VDLAudioTarget::WMA){
			$cmdStr.= " -oac lavc -lavcopts acodec=wmav2";
			if($aud->_bitRate)
				$cmdStr.= ":abitrate=".$aud->_bitRate;
		}

		if($aud->_sampleRate)
			$cmdStr.= " -srate ".$aud->_sampleRate;

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
			case VDLContainerTarget::WMV:
				$format = "asf";
				break;
			case VDLContainerTarget::MPEGTS:
			case VDLContainerTarget::M2TS:
			case VDLContainerTarget::APPLEHTTP:
				$format = "mpg";
				break;
			default:
				$format = $con->_id;
				break;
		}
		
		// This will not work for mp4 - TO FIX
		$cmdStr.= " -of lavf -lavfopts format=".$format;

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
//		$ffQsettings = " -qcomp 0.6 -qmin 10 -qmax 50 -qdiff 4";
		switch($videoObject->_id) {
		case VDLVideoTarget::H264:
			$h264params.= " -ovc x264 -x264encopts ";
			if($videoObject->_bitRate) {
				$h264params.= "bitrate=".$videoObject->_bitRate;
				$h264params.= ":";
				if($videoObject->_bitRate<VDLConstants::LowBitrateThresHold) {
					$h264params.= "crf=30:";
				}
			}
			$h264params .= "subq=2:8x8dct:frameref=2:bframes=3:b_pyramid=1:weight_b:threads=auto";
			break;
		case VDLVideoTarget::H264B:
			$h264params.= " -ovc x264 -sws 9 -x264encopts ";
			if($videoObject->_bitRate) {
				$h264params.= " bitrate=".$videoObject->_bitRate;
				$h264params.= ":";
				if($videoObject->_bitRate<VDLConstants::LowBitrateThresHold) {
					$h264params.= "crf=30:";
				}
			}
			$h264params.= "subq=2:frameref=6:bframes=0:threads=auto:nocabac:level_idc=30:global_header:partitions=all:trellis=1:chroma_me:me=umh";
			break;
		case VDLVideoTarget::H264M:
			$h264params.= " -ovc x264 -sws 9 -x264encopts ";
			if($videoObject->_bitRate) {
				$h264params.= " bitrate=".$videoObject->_bitRate;
				$h264params.= ":";
				if($videoObject->_bitRate<VDLConstants::LowBitrateThresHold) {
					$h264params.= "crf=30:";
				}
			}
			$h264params.= "subq=5:frameref=6:bframes=3:threads=auto:level_idc=30:global_header:partitions=all:trellis=1:chroma_me:me=umh";
			break;
		case VDLVideoTarget::H264H:				
			$h264params.= " -ovc x264 -sws 9 -x264encopts ";
			if($videoObject->_bitRate) {
				$h264params.= " bitrate=".$videoObject->_bitRate;
				$h264params.= ":";
				if($videoObject->_bitRate<VDLConstants::LowBitrateThresHold) {
					$h264params.= "crf=30:";
				}
			}
			$h264params.= "subq=7:frameref=6:bframes=3:b_pyramid=1:weight_b=1:threads=auto:level_idc=30:global_header:8x8dct:trellis=1:chroma_me:me=umh";
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
		 * Remove mencoder, encoding.com and cli_encode
		 * for audio only flavors
		 */
		if($target->_video==null) {
			$warnings[VDLConstants::AudioIndex][] = //"The transcoder (".$key.") does not handle properly DAR<>PAR.";
				VDLWarnings::ToString(VDLWarnings::TranscoderLimitation, $this->_id);
			return true;
		}

			// Encryption unsupported by Mencoder
		if($target->_isEncrypted==true){
			$warnings[VDLConstants::ContainerIndex][] = 
				VDLWarnings::ToString(VDLWarnings::TranscoderLimitation, $this->_id)."(encryption)";
			return true;
		}
		return false;
	}
}

<?php
/**
 * @package plugins.quickTimeTools
 * @subpackage lib
 */
class VDLTranscoderQTPTools extends VDLOperatorBase {
    public function __construct($id, $name=null, $sourceBlacklist=null, $targetBlacklist=null) {
    	if(is_null($targetBlacklist)){
			parent::__construct($id,$name,$sourceBlacklist, 
				array(
					VDLConstants::ContainerIndex=>array(VDLContainerTarget::WMV, VDLContainerTarget::ISMV),
					VDLConstants::VideoIndex=>array("wvc1", VDLVideoTarget::WMV2,VDLVideoTarget::WMV3,VDLVideoTarget::FLV,VDLVideoTarget::VP6)));
    	}
    	else
    		parent::__construct($id,$name,$sourceBlacklist,$targetBlacklist);
    }

    public function GenerateCommandLine(VDLFlavor $design, VDLFlavor $target, $extra=null)
	{
/*
pcastaction encode --input=/Users/macuser/Downloads/APCN.pr_the_dark_knight_now_showing_DM.mov --output=/web/content/tmp/apcn.h264_hint_server.mp4 --encoder=h264_hint_server --basedir=~/Downloads/tmp
pcastaction encode --input=__input__ --output=__outPut__ --encoder=desktop --basedir=~/web/content/tmp

 */
/*
~/Downloads/qt_tools/pieces/bin/qt_export icod.mov --video=avc1,,100  --replacefile -1 --datarate=2000 aaa500.mp5
 */
 // ~/Downloads/qt_tools/pieces/bin/qt_export --video=avc1,,100 --audio=mp4a APCN.pr_the_dark_knight_now_showing_DM.mov aaa227icod.mp5  --replacefile -1
//		return "qqqqqwwwww"; //$design->ToString();
	$cmdStr = null;
// rem ffmpeg -i <infilename> -vcodec flv   -r 25 -b 500k  -ar 22050 -ac 2 -acodec libmp3lame -f flv -t 60 -y <outfilename>
$vcodecParams = "fl";
$format = "fl";
$acodec = "libmp3lam";

		$cmdStr = " ".VDLCmdlinePlaceholders::InFileName;

		if($target->_video){
			$vid = $target->_video;
			switch($vid->_id){
				case VDLVideoTarget::H263:
					$vcodecParams = "h263";
					break; 
				case VDLVideoTarget::H264:
				case VDLVideoTarget::H264B:
				case VDLVideoTarget::H264M:
				case VDLVideoTarget::H264H:
					$vcodecParams = "avc1";
					break; 				
				case VDLVideoTarget::MPEG4:
					$vcodecParams = "mp4v";
					break;
				default:
					$vcodecParams="";
					break;
			}
			
			$cmdStr .= " --video=".$vcodecParams;

			$cmdStr .= ",";
			if($vid->_frameRate!==null && $vid->_frameRate>0){
				$cmdStr .= $vid->_frameRate;
			}

			$cmdStr .= ",100";
			
			if($vid->_bitRate){
				$cmdStr .= " --datarate=".round($vid->_bitRate/8);
			}
			if($vid->_gop!==null && $vid->_gop>0){
				$cmdStr .= " --keyframerate=".$vid->_gop;
			}
/*			if($vid->_width!=null && $vid->_height!=null){
				$cmdStr = $cmdStr." -s ".$vid->_width."x".$vid->_height;
			}
			if($vid->_scanType!==null && $vid->_scanType>0){ // ScanType 0:progressive, 1:interlaced
				$cmdStr = $cmdStr." -deinterlace";
			}
*/
		}
		else {
			$cmdStr .= " --video=0";
		}

		if(0 && $target->_audio) {
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
				case VDLAudioTarget::COPY:
					$acodec = "copy";
					break;
				default:
					$acodec="MAC6";
					break;
			}
			$cmdStr .= " --audio=".$acodec;
			$cmdStr .= ",";
			if($aud->_sampleRate!==null && $aud->_sampleRate>0){
				$cmdStr .= $aud->_sampleRate;
			}
			$cmdStr .= ",";  // instead of bits-per-sample
			$cmdStr .= ",";
			if($aud->_channels!==null && $aud->_channels>0){
				$cmdStr .= $aud->_channels;
			}
//			if($aud->_bitRate==null && $aud->_bitRate>0){
//				$cmdStr = $cmdStr." -ab ".$aud->_bitRate."k";
//			}
		}
		else {
			//$cmdStr .= " --audio=0";
		}
		
		if($target->_clipStart!==null && $target->_clipStart>0){
//			$cmdStr .= " --strtrtduration=".$target->_clipStart;
		}

		if($target->_clipDur!==null && $target->_clipDur>0){
			$cmdStr .= " --duration=".$target->_clipDur;
		}

		if(0 && $target->_container) {
			$cont = $target->_container;
			switch($cont->_id){
				case VDLContainerTarget::FLV:
					$format = "flv";
					break;
				case VDLContainerTarget::AVI:
				case VDLContainerTarget::MP4:
				case VDLContainerTarget::_3GP:
				case VDLContainerTarget::MOV:
				case VDLContainerTarget::MP3:
				case VDLContainerTarget::OGG:
				case VDLContainerTarget::WEBM:
					$format = $cont->_id;
					break;
				case VDLContainerTarget::WMV:
					$format = "asf";
					break;
				case VDLContainerTarget::MKV:
					$format = "matroska";
					break;
				default:
					$format = "";
					break;
			}
			$cmdStr = $cmdStr." -f ".$format;
		}
	
		if($extra)
			$cmdStr .= " ".$extra;
		
		$cmdStr .= " --replacefile -1 ".VDLCmdlinePlaceholders::OutFileName;

		return $cmdStr;
	}
	
    /* ---------------------------
	 * CheckConstraints
	 */
	public function CheckConstraints(VDLMediaDataSet $source, VDLFlavor $target, array &$errors=null, array &$warnings=null)
	{
	    return parent::CheckConstraints($source, $target, $errors, $warnings);
	}
}


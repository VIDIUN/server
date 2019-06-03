<?php
/**
 * @package plugins.avidemux
 * @subpackage lib
 */
class VDLOperatorAvidemux extends VDLOperatorBase {

    public function GenerateCommandLine(VDLFlavor $design, VDLFlavor $target, $extra=null)
	{
// avidemux2_cli.exe --force-alt-h264 n --autoindex --rebuild-index --nogui --force-smart --load split_810.mp4 --append split_840.mp4 --save ccc.mp4
// avidemux2_cli.exe --force-alt-h264 n --autoindex --rebuild-index --nogui --force-smart --load split_810.mp4 --append split_840.mp4 --save ccc.mp4
//avidemux2 --load input.avi --audio-process --audio-normalize --audio-resample 44100 --audio-codec MP2 --audio-bitrate 224 --output-format PS --video-process --vcd-res --video-codec VCD --save output.mpg --quit

			$cmdStr = null;
// rem ffmpeg -i <infilename> -vcodec flv   -r 25 -b 500k  -ar 22050 -ac 2 -acodec libmp3lame -f flv -t 60 -y <outfilename>
$vcodecParams = "fl";
$format = "fl";
$acodec = "libmp3lam";

		$cmdStr = "--force-alt-h264 n --autoindex --rebuild-index --nogui --force-smart --load ".VDLCmdlinePlaceholders::InFileName;
		$cmdStr.= " --video-codec x264 --save ".VDLCmdlinePlaceholders::OutFileName." --quit";
	return $cmdStr;
		if($target->_video){
			$vid = $target->_video;
			switch($vid->_id){
				case VDLVideoTarget::H263:
					$vcodecParams = "263";
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
}


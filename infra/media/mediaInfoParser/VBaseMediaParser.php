<?php
/**
 * @package server-infra
 * @subpackage Media
 */
abstract class VBaseMediaParser
{
	const MEDIA_PARSER_TYPE_MEDIAINFO = '0';
	const MEDIA_PARSER_TYPE_FFMPEG = '1';
	
	const ERROR_NFS_FILE_DOESNT_EXIST = 21; // VidiunBatchJobAppErrors::NFS_FILE_DOESNT_EXIST
	const ERROR_EXTRACT_MEDIA_FAILED = 31; // VidiunBatchJobAppErrors::EXTRACT_MEDIA_FAILED
	
	/**
	 * @var string
	 */
	protected $filePath;
	
	/**
	 * @param string $type
	 * @param string $filePath
	 * @param VSchedularTaskConfig $taskConfig
	 * @return VBaseMediaParser
	 */
	public static function getParser($type, $filePath, VSchedularTaskConfig $taskConfig, VidiunBatchJob $job)
	{
		switch($type)
		{
			case self::MEDIA_PARSER_TYPE_MEDIAINFO:
				return new VMediaInfoMediaParser($filePath, $taskConfig->params->mediaInfoCmd);
				
			case self::MEDIA_PARSER_TYPE_FFMPEG:
				return new VFFMpegMediaParser($filePath, $taskConfig->params->FFMpegCmd);
				
			default:
				return VidiunPluginManager::loadObject('VBaseMediaParser', $type, array($job, $taskConfig));
		}
	}
	
	/**
	 * @param string $filePath
	 */
	public function __construct($filePath)
	{
		$this->filePath = $filePath;
	}
	
	/**
	 * @return VidiunMediaInfo
	 */
	public function getMediaInfo()
	{
		$output = $this->getRawMediaInfo();
		return $this->parseOutput($output);
	}

	/**
	 * @param string $filePath
	 */
	public function setFilePath($filePath)
	{
		$this->filePath = $filePath;
	}

	/**
	 * @return string
	 */
	public function getRawMediaInfo()
	{
		$cmd = $this->getCommand();
		VidiunLog::debug("Executing '$cmd'");
		$output = shell_exec($cmd);
		if (trim($output) === "")
			throw new vApplicativeException(VBaseMediaParser::ERROR_EXTRACT_MEDIA_FAILED, "Failed to parse media using " . get_class($this));
			
		return $output;
	}
	
	/**
	 * 
	 * @param VidiunMediaInfo $mediaInfo
	 * @return VidiunMediaInfo
	 */
	public static function removeUnsetFields(VidiunMediaInfo $mediaInfo)
	{
		foreach($mediaInfo as $key => $value) {
           	if(!isset($value)){
          		unset($mediaInfo->$key);
           	}
       	}
		return $mediaInfo;
	}
	
	/**
	 * 
	 * @param VidiunMediaInfo $mIn
	 * @param VidiunMediaInfo $mOut
	 * @return VidiunMediaInfo
	 */
	public static function copyFields(VidiunMediaInfo $mIn, VidiunMediaInfo $mOut)
	{
		foreach($mIn as $key => $value) {
			$mOut->$key = $mIn->$key;
       	}
		return $mOut;
	}

	/**
	 * 
	 * @param VidiunMediaInfo $m1
	 * @param VidiunMediaInfo $m2
	 */
	public static function compareFields($m1, $m2)
	{
		$fields = array(
"fileSize",
"containerFormat",
"containerId",
"containerDuration",
"containerBitRate",

"audioFormat",
"audioCodecId",
"audioDuration",
"audioBitRate",
"audioChannels",
"audioSamplingRate",
"audioResolution",

"videoFormat",
"videoCodecId",
"videoDuration",
"videoBitRate",
"videoBitRateMode",
"videoWidth",
"videoHeight",
"videoFrameRate",
"videoDar",
"videoRotation",
"scanType",
		);

$container_format_synonyms = array(
	array("mp4","mpeg4"),
	array("flv","sorenson spark","flash video"),
	array("asf","windows media"),
	array("mpeg","mpegps"),
	array("mpeg audio","mp3"),
);
$video_format_synonyms = array(
	array("h264","avc","avc1"),
	array("mp4","mpeg4"),
	array("mpeg4 visual","mpeg4"),
	array("flv","sorenson spark","flash video"),
	array("vc1","wmv3"),
	array("mpeg video","mpeg2video","mpeg1video","mpegps"),
	array("intermediate codec","apple intermediate codec","icod","aic"),
	array("vp6","vp6f"),
	array("ms video","msvideo1"),
);
$video_codec_id_synonyms = array(
	array("4","[0][0][0][0]"),
	array("2","[0][0][0][0]","[2][0][0][0]"),
	array("20","mp4v"),
	array("v_vp8","[0][0][0][0]"),
	array("wmv3","[0][0][0][0]"),
);
$audio_format_synonyms = array(
	array("mpeg audio","mp3", "mp2"),
	array("wma","wmapro"),
	array("wma","wmav2","a[1][0][0]"),
	array("pcm","pcm_s16le","pcm_s16be"),
	array("2","[0][0][0][0]"),
);
$audio_codec_id_synonyms = array(
	array("aac","40","mp4a"),
	array("161","a[1][0][0]"),
	array("50","p[0][0][0]"),
	array("162","b[1][0][0]"),
	array("55","u[0][0][0]"),
	array("2","[0][0][0][0]"),
	array("a_vorbis","[0][0][0][0]"),
	array("5","6","[0][0][0][0]"),
	array("1","[1][0][0][0]"),
	array("4","[4][0][0][0]"),
);

		if(!isset($m1) && !isset($m2)) {
			return("(missing,missing)");
		}
		else if(!isset($m1)) {
			return("(missing,exists)");
		}
		else if(!isset($m2)) {
			return("(exists,missing)");
		}
		
		$msg = null;
		foreach ($fields as $f){
			if(isset($m1->$f) && isset($m2->$f)){
				$f1 = str_replace(array(".","-"),array("",""), $m1->$f);
				$f2 = str_replace(array(".","-"),array("",""), $m2->$f);
				if($f1==$f2)
					continue;
				
				if(is_numeric($m1->$f) && is_numeric($m2->$f)){
					if($m1->$f>0) {
						if(abs(1-$m2->$f/$m1->$f)<0.01)
							continue;
					}
					if(stristr($f, "duration")!=false){
						$a1 = $m1->$f - $m1->$f%1000;
						$a2 = $m2->$f - $m2->$f%1000;
						if($a1==$a2)
							continue;
					}
				}
				
				if($f=="containerFormat" && self::isSynonym($f1, $f2, $container_format_synonyms)==true){
					continue;
				}
				
				if($f=="videoFormat" && self::isSynonym($f1, $f2, $video_format_synonyms)==true){
					continue;
				}
				
				if($f=="videoCodecId" && self::isSynonym($f1, $f2, $video_codec_id_synonyms)==true){
					continue;
				}
				
				if($f=="audioFormat" && self::isSynonym($f1, $f2, $audio_format_synonyms)==true){
					continue;
				}
				
				if($f=="audioCodecId" && self::isSynonym($f1, $f2, $audio_codec_id_synonyms)==true){
					continue;
				}
				
				$msg.="$f(".$m1->$f.",".$m2->$f."),";
			}
			else if(!(isset($m1->$f) && isset($m2->$f))){
				continue;
			}
			else if(isset($m1->$f)) {
				$msg.="$f(".$m1->$f.",missing),";
			}
			else {
				$msg.="$f(missing,".$m2->$f."),";	
			}
		}
		return ($msg);
	}
	
	/**
	 * 
	 * @param unknown_type $f1
	 * @param unknown_type $f2
	 * @param unknown_type $synonyms
	 * @return boolean
	 */
	private static function isSynonym($f1, $f2, $synonyms)
	{
		foreach($synonyms as $syn){
			if(in_array($f1, $syn) && in_array($f2, $syn)){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 
	 * @param VidiunMediaInfo $mediaInfo
	 * @return boolean
	 */
	public static function isVideoSet(VidiunMediaInfo $mediaInfo)
	{
		if(isset($mediaInfo->videoCodecId))
			return true;
		if(isset($mediaInfo->videoFormat))
			return true;
		if(isset($mediaInfo->videoDuration))
			return true;
		if(isset($mediaInfo->videoBitRate))
			return true;
		
		
		if(isset($mediaInfo->videoWidth))
			return true;
		if(isset($mediaInfo->videoHeight))
			return true;
		if(isset($mediaInfo->videoFrameRate))
			return true;
		if(isset($mediaInfo->videoDar))
			return true;
		
		return false;
	}
	
	/**
	 * 
	 * @param VidiunMediaInfo $mediaInfo
	 * @return boolean
	 */
	public static function isAudioSet(VidiunMediaInfo $mediaInfo)
	{
		if(isset($mediaInfo->audioCodecId))
			return true;
		if(isset($mediaInfo->audioFormat))
			return true;
		if(isset($mediaInfo->audioDuration))
			return true;
		if(isset($mediaInfo->audioBitRate))
			return true;
	
		if(isset($mediaInfo->audioSamplingRate))
			return true;
		if(isset($mediaInfo->audioResolution))
			return true;
		if(isset($mediaInfo->audioChannels))
			return true;
		
		return false;
	}
	
	/**
	 * 
	 * @param string
	 * @return int
	 */
	protected static function convertDuration2msec($str)
	{
		preg_match_all("/(([0-9]*)h ?)?(([0-9]*)mn ?)?(([0-9]*)s ?)?(([0-9]*)ms ?)?/",
			$str, $res);
			
		$hour = @$res[2][0] ? @$res[2][0] : 0;
		$min  = @$res[4][0] ? @$res[4][0] : 0;
		$sec  = @$res[6][0] ? @$res[6][0] : 0;
		$msec = @$res[8][0] ? @$res[8][0] : 0;
		
		$rv = ($hour*3600 + $min*60 + $sec)*1000 + $msec;
		if($rv==0){
			sscanf($str,"%d:%d:%f", $hour, $min, $sec);
			$rv = ($hour*3600 + $min*60 + $sec)*1000 ;
		}
		
		return (int)$rv;
	}
	
	/**
	 * Set 'empty' video params fields with ffmpeg/ffprobe values
	 * @param VidiunMediaInfo $mediaInfo
	 * 		  VidiunMediaInfo $mediaInfoFix
	 */
	protected static function setVideoParams(VidiunMediaInfo $mediaInfo, VidiunMediaInfo $mediaInfoFix)
	{
		$fieldsArr = array("videoCodecId","videoFormat","videoDuration","videoBitRate","videoWidth","videoHeight","videoFrameRate","videoDar");
		foreach($fieldsArr as $field) {
			if(isset($mediaInfoFix->$field))
				$mediaInfo->$field = $mediaInfoFix->$field;
		}
	}
	
	/**
	 * Set 'empty' audio params fields with ffmpeg/ffprobe values
	 * @param VidiunMediaInfo $mediaInfo
	 * 		  VidiunMediaInfo $mediaInfoFix
	 */
	protected static function setAudioParams(VidiunMediaInfo $mediaInfo, VidiunMediaInfo $mediaInfoFix)
	{
		$fieldsArr = array("audioCodecId","audioFormat","audioDuration","audioBitRate","audioSamplingRate","audioResolution","audioChannels");
		foreach($fieldsArr as $field) {
			if(isset($mediaInfoFix->$field))
				$mediaInfo->$field = $mediaInfoFix->$field;
		}
	}
	
	/**
	 * Adjust/fix duration fields with ffmpeg/ffprobe values
	 * @param VidiunMediaInfo $mediaInfo
	 * 		  VidiunMediaInfo $mediaInfoFix
	 */
	protected static function adjustDurations(VidiunMediaInfo $mediaInfo, VidiunMediaInfo $mediaInfoFix)
	{
		$fieldsArr = array("audioDuration","videoDuration","containerDuration");
		foreach($fieldsArr as $field) {
			if(!(isset($mediaInfoFix->$field) && $mediaInfoFix->$field>0))
				return;
			
			if(!(isset($mediaInfo->$field) && $mediaInfo->$field>0) 
			|| (($ratio=$mediaInfo->$field/$mediaInfoFix->$field)<0.95 || $ratio>1.05)) {
				$mediaInfo->$field = $mediaInfoFix->$field;
			}
		}
	}
	
	/**
	 * @return string
	 */
	protected abstract function getCommand();
	
	/**
	 * 
	 * @param string $output
	 * @return VidiunMediaInfo
	 */
	protected abstract function parseOutput($output);
}


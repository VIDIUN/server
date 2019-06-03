<?php
/**
 * @package plugins.mp4box
 * @subpackage lib
 */
class VOperationEngineMp4box  extends VSingleOutputOperationEngine
{
	public function __construct($cmd, $outFilePath)
	{
		parent::__construct($cmd,$outFilePath);
		VidiunLog::info(": cmd($cmd), outFilePath($outFilePath)");
	}

	/***************************
	 * @param 
	 * @return string
	 */
	protected function getCmdLine() 
	{
		$exeCmd =  parent::getCmdLine();

		if(strstr($exeCmd, VDLOperatorMp4box::ACTION_EMBED_SUBTITLES)!==FALSE) {
			$captionsStr = null;
			{
					// impersonite
				VBatchBase::impersonate($this->job->partnerId);
				
				$captionsStr = $this->buildSubTitleCommandParam($this->data);
					// un-impersonite
				VBatchBase::unimpersonate();
			}
			if(isset($captionsStr)){
				$exeCmd = str_replace(
						array(VDLOperatorMp4box::ACTION_EMBED_SUBTITLES, VDLOperatorMp4box::SUBTITLE_PLACEHOLDER), 
						array("", $captionsStr), 
						$exeCmd);
			}
			else if(!(isset($this->operator) && isset($this->operator->isOptional) && $this->operator->isOptional>0)){
				$this->message.=".".print_r($this->operator,1);
				throw new VOperationEngineException($this->message);
			}
		}
		else if(strstr($exeCmd, VDLOperatorMp4box::ACTION_HINT)!==FALSE) {
			$exeCmd = str_replace (VDLOperatorMp4box::ACTION_HINT,"", $exeCmd);
		}
		return $exeCmd; 
	}

	/***************************
	 * buildSubTitleCommandParam
	 *
	 * @param VidiunConvartableJobData $data
	 * @return 
	 */
	private function buildSubTitleCommandParam(VidiunConvartableJobData $data)
	{//		$cmdStr.= " -add ".VDLCmdlinePlaceholders::OutFileName.".temp.srt:hdlr=sbtl:lang=$lang:group=0:layer=-1";
		$jobMsg = null;
		$captionsArr = VConversionEngineFfmpeg::fetchEntryCaptionList($data, $jobMsg);
		if(!isset($captionsArr) || count($captionsArr)==0){
			VidiunLog::log($jobMsg);
			$this->message = $jobMsg;
			return null;
		}
		
		$captionsStr = null;
		$addedSubs=0;
		foreach($captionsArr as $lang=>$captionFileUrl){
			$captionFilePath = VConversionEngineFfmpeg::fetchCaptionFile($captionFileUrl, $data->destFileSyncLocalPath.".temp.$lang.srt");

			if(!isset($captionFilePath)){
				continue;
			}
			/*
			 * group - "An integer that specifies a group or collection of tracks. If this field is 0 there is no information
			 * 	on possible relations to other tracks. If this field is not 0, it should be the same for tracks that contain 
			 * 	alternate data for one another and different for tracks belonging to different such groups. Only one track 
			 * 	within an alternate group should be played or streamed at any one time, and must be distinguishable from other 
			 * 	racks in the group via attributes such as bitrate, codec, language, packet size etc. A group may have only one member. "
			 *	To follow that desc, the group id for all subtitles would be set to 1.
			 *	Apart from the first subs track, all the others would be tagged with 'disabled', otherwise the older iOS devices (pre 5.x)
			 *	does not handle it properly.
			 * layer - "Specifies the front-to-back ordering of video tracks; tracks with lower numbers are closer to the viewer. 
			 *	0 is the normal value, and -1 would be in front of track 0, and so on."
			 *	layer=-1, closest to the viewer
			 */
			$captionsStr.= " -add ".$captionFilePath.":hdlr=sbtl:lang=".$lang.":group=1:layer=-1";
			if($addedSubs>0) {
				$captionsStr.= ":disabled";
			}
			$addedSubs++;
		}

		if(!isset($captionsStr))
		{
			$this->message = ("Error: missing caption data or files.");
			return null;
		}
		return $captionsStr;
	}
	
}

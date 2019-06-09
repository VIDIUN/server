<?php

	/* ===========================
	 * VDLOperatorWrapper
	 */
class VDLOperatorWrapper extends VDLOperatorBase {
    public function __construct($id, $name=null, $sourceBlacklist=null, $targetBlacklist=null) {
    	$srcBlacklist = $sourceBlacklist;
		if(is_null($sourceBlacklist) && array_key_exists($id, VDLConstants::$TranscodersSourceBlackList)) {
			$srcBlacklist = VDLConstants::$TranscodersSourceBlackList[$id];
		}
		$trgBlacklist = $targetBlacklist;
		if(is_null($targetBlacklist) && array_key_exists($id, VDLConstants::$TranscodersTargetBlackList)) {
			$trgBlacklist = VDLConstants::$TranscodersTargetBlackList[$id];
		}
    	parent::__construct($id,$name,$srcBlacklist,$trgBlacklist);
    }

	public function GenerateCommandLine(VDLFlavor $predesign, VDLFlavor $target, $extra=null)
	{
//		$cmdLineGenerator = $target->SetTranscoderCmdLineGenerator($predesign);
		$cmdLineGenerator = new VDLTranscoderCommand($predesign, $target);
		$params = new VDLOperationParams();
		$params->Set($this->_id, $extra);
		if(isset($predesign->_video))
			return $cmdLineGenerator->Generate($params, $predesign->_video->_bitRate);
		else 
			return $cmdLineGenerator->Generate($params, 0);
	}

    /* ---------------------------
	 * CheckConstraints
	 */
	public function CheckConstraints(VDLMediaDataSet $source, VDLFlavor $target, array &$errors=null, array &$warnings=null)
	{
//No need for 'global' check, each engine can check for itself
//		if(parent::CheckConstraints($source, $target, $errors, $warnings)==true)
//			return true;

		if($this->_id==VDLTranscoders::FFMPEG_AUX) {
			$transcoder = new VDLOperatorFfmpeg2_2($this->_id);
			if($transcoder->CheckConstraints($source, $target, $errors, $warnings)==true)
				return true;
		}
			
		if($this->_id==VDLTranscoders::FFMPEG) {
			$transcoder = new VDLOperatorFfmpeg2_7_2($this->_id);
			if($transcoder->CheckConstraints($source, $target, $errors, $warnings)==true)
				return true;
		}
	
		
		if($this->_id==VDLTranscoders::MENCODER) {
			$transcoder = new VDLOperatorMencoder($this->_id);
			if($transcoder->CheckConstraints($source, $target, $errors, $warnings)==true)
				return true;
		}
	
		
		if($this->_id==VDLTranscoders::ON2) {
			$transcoder = new VDLOperatorOn2($this->_id);
			if($transcoder->CheckConstraints($source, $target, $errors, $warnings)==true)
				return true;
		}
	
		/*
		 * Remove encoding.com - it is no longer supported
		 */
		if($this->_id==VDLTranscoders::ENCODING_COM){
			$warnings[VDLConstants::ContainerIndex][] =
				VDLWarnings::ToString(VDLWarnings::TranscoderLimitation, $this->_id)."(unsupported transcoder)";
			return true;
		}
		
		/*
		 * Prevent invalid copy attempts, that might erronously end up with 'false-positive' result
		 */
		if((isset($target->_video) && $target->_video->_id==VDLVideoTarget::COPY)
		|| (isset($target->_audio) && $target->_audio->_id==VDLAudioTarget::COPY)){
			if($target->_container->_id==VDLContainerTarget::FLV){
				$rvArr=$source->ToTags(array("web"));
				if(count($rvArr)==0){
					$errStr = "Copy to Target format:FLV, Source:".$source->ToString();
					$target->_errors[VDLConstants::ContainerIndex][] = 
						VDLErrors::ToString(VDLErrors::InvalidRequest, $errStr);
					return true;
				}
			}
		}
		
		return false;	
	}
}


	/* ===========================
	 * VDLTranscoderCommand
	 */
class VDLTranscoderCommand {
	
	private $_design;
	private $_target;
			
	public function __construct(VDLFlavor $design, VDLFlavor $target)
	{
		$this->_design = $design;
		$this->_target = $target;
	}	
	
	/* ---------------------------
	 * Generate
	 */
	public function Generate(VDLOperationParams $transParams, $maxVidRate)
	{
		$cmd=null;
		switch($transParams->_id){
			case VDLTranscoders::VIDIUN:
				$cmd=$transParams->_id;
				break;
			case VDLTranscoders::ON2:
				$cmd=$this->CLI_Encode($transParams->_extra);;
				break;
			case VDLTranscoders::FFMPEG:
			case VDLTranscoders::FFMPEG_VP8:
				$cmd=$this->FFMpeg($transParams->_extra);
				break;
			case VDLTranscoders::MENCODER:
				$cmd=$this->Mencoder($transParams->_extra);
				break;
			case VDLTranscoders::ENCODING_COM:
				$cmd=$transParams->_id;
				break;
			case VDLTranscoders::FFMPEG_AUX:
				$cmd=$this->FFMpeg_aux($transParams->_extra);
				break;
			case VDLTranscoders::EE3:
				$cmd=$this->EE3($transParams->_extra);
				break;
		}
		return $cmd;
	}
	
	/* ---------------------------
	 * FFMpeg
	 */
	public function FFMpeg($extra=null)
	{
		$transcoder = new VDLOperatorFfmpeg2_7_2(VDLTranscoders::FFMPEG); 
		return $transcoder->GenerateCommandLine($this->_design,  $this->_target,$extra);
	}

	/* ---------------------------
	 * Mencoder
	 */
	public function Mencoder($extra=null)
	{
		$transcoder = new VDLOperatorMencoder(VDLTranscoders::MENCODER); 
		return $transcoder->GenerateCommandLine($this->_design,  $this->_target,$extra);
	}

	/* ---------------------------
	 * CLI_Encode
	 */
	public function CLI_Encode($extra=null)
	{
		$transcoder = new VDLOperatorOn2(VDLTranscoders::ON2); 
		return $transcoder->GenerateCommandLine($this->_design,  $this->_target,$extra);
	}
	
	/* ---------------------------
	 * Encoding_com
	 */
	public function Encoding_com($extra=null)
	{
		return $this->CLI_Encode($extra);
	}

	/* ---------------------------
	 * FFMpeg_aux
	 */
	public function FFMpeg_aux($extra=null)
	{/**/
		$transcoder = new VDLOperatorFfmpeg2_2(VDLTranscoders::FFMPEG_AUX); 
		return $transcoder->GenerateCommandLine($this->_design,  $this->_target,$extra);
	}

	/* ---------------------------
	 * EE3
	 */
	public function EE3($extra=null)
	{
		$ee3 = new VDLExpressionEncoder3(VDLTranscoders::EE3);
		return $ee3->GeneratePresetFile($this->_target);
	}

}

?>

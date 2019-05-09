<?php
//include_once("VDLCommon.php");
//include_once("VDLMediaDataSet.php");

/* ===========================
 * VDLFlavor
 */

class VDLFlavor extends VDLMediaDataSet {

	const RedundantFlagBit = 1;
	const BitrateNonComplyFlagBit = 2;
	const MissingContentNonComplyFlagBit = 4;
	const ForceCommandLineFlagBit = 8;
	const FrameSizeNonComplyFlagBit = 16;
	
	const ENCRYPTION_KEY_PLACEHOLDER = "__ENCRYPTION_KEY__";
	const ENCRYPTION_KEY_ID_PLACEHOLDER = "__ENCRYPTION_KEY_ID__";

	/* ---------------------
	 * Data
	 */
	
	public	$_flags=0;
	public	$_isTwoPass=false;
	public  $_clipStart=null;
	public 	$_clipDur=null;
				/* 
				 * Contrary to to the 'clipDur' data member, the 'explicitClipDur' can not be 0 or null,
				 * for flavors that have a clip action.
				 * clipDur==0/null means that the clip should end when the source is finised.
				 * Some transcoders require 'explicit' duration (EE3), even in those cases.
				 * For those cases I have set this data member.
				 * Although the 'clipDur' can be changed to act in this way, I prefered to not to touch
				 * the origina logic (that works for ffmpeg,mec, on2, vlc), but rather set this new data member
				 */
	public	$_explicitClipDur=null; 
				/*
				 * To clip a file, we have to seek to required position.
				 * There are fast and slow seek method.
				 * Sveral fromats (mpeg2, theora), don't support fast seeks 
				 */
	public 	$_fastSeekTo = true;
	
	public $_optimizationPolicy = VDLOptimizationPolicy::BitrateFlagBit;
	
	public $_isEncrypted = 0;	// CENC encryption, 0:not encrypted, 1:encrypt >10sec, 2:encrypt all
	
	public	$_transcoders = array();

		/* --------------------------
		 * following fields are for flavorOutputParams
		 * to be moved to the VDLWrap
		 */
	public 	$_id = null;
	public 	$_type = 1;
	public  $_tags=null;
	public  $_name=null;
	public	$_engineVersion=0;
	
	public	$_cdlObject = null; /* To avoid duplicating of fields that are only used for transfer 
									to flavorOutputParams objects, the original CDL object
									is saved on the VDLFlavor object. The required fields are 
									copied in the VDLWrap 
								*/ 
	/* --------------------------- */
	
	/* ----------------------
	 * Cont/Dtor
	 */
	public function __construct() {
		parent::__construct();
	}
	public function __destruct() {
	}
	public function __clone() {
		if(!is_null($this->_container)) $this->_container = clone $this->_container;
		if(!is_null($this->_video)) $this->_video = clone $this->_video;
		if(!is_null($this->_audio)) $this->_audio = clone $this->_audio;
		if(!is_null($this->_cdlObject)) $this->_cdlObject = clone $this->_cdlObject;
	}
	
	/* ----------------------
	 * ProcessRedundancy
	 */
	public function ProcessRedundancy(VDLFlavor $prevFlavor){
		$rv = true;
		/*
		 * If no video => keep the flavor
		 */
		if($this->_video!=null && $prevFlavor->_video!=null) {
			/*
			 * The previous flavor should be atleast FlavorBitrateRedundencyFactor
			 * away, else - remove the current flavor.
			 */
			$redundRatio = $this->_video->_bitRate/$prevFlavor->_video->_bitRate;
			if($redundRatio>1) $redundRatio = 1/$redundRatio;
			if($redundRatio>VDLConstants::FlavorBitrateRedundencyFactor) {
				$this->_flags = $this->_flags | VDLFlavor::RedundantFlagBit;

				$this->_warnings[VDLConstants::VideoIndex][]= //"Redundant bitrate";
				VDLWarnings::ToString(VDLWarnings::RedundantBitrate);
			}
			else
			$rv = false;
		}

		if($this->_audio!=null && $prevFlavor->_audio!=null) {
			if($this->_audio->_bitRate==$prevFlavor->_audio->_bitRate) {
				$this->_flags = $this->_flags | VDLFlavor::RedundantFlagBit;

				$this->_warnings[VDLConstants::AudioIndex][]= //"Redundant bitrate";
				VDLWarnings::ToString(VDLWarnings::RedundantBitrate);
			}
			else
			$rv = false;
		}

		return $rv;
	}

	/* ---------------------------
	 * ValidateFlavor
	 */
	public function ValidateFlavor()
	{
		return parent::Initialize();
	}

	/* ---------------------------
	 * ToString
	 */
	public function ToString(){
		$rvStr = "flag($this->_flags)";
		if($this->_clipStart) {
			$rvStr .= ",clpStr($this->_clipStart)";
		}
		if($this->_clipDur) {
			$rvStr .= ",clpDur($this->_clipDur)";
		}
		$rvStr .= ",".parent::ToString();
		if(count($this->_errors)){
			$rvStr = $rvStr.",ERRS(".VDLUtils::arrayToString($this->_errors).")";
		}
		if(count($this->_warnings)){
			$rvStr = $rvStr.",WRNS(".VDLUtils::arrayToString($this->_warnings).")";
		}
		if(count($this->_transcoders)){
			$rvStr = $rvStr.",TRNS(".VDLUtils::arrayToString($this->_transcoders).")";
		}
		return $rvStr;
	}

	/* ---------------------------
	 * GenerateTarget
	 */
	public function GenerateTarget(VDLMediaDataSet $source) {
		if($source==null || !$source->IsDataSet() || $this->_flags&self::ForceCommandLineFlagBit) {
			VidiunLog::log("FORCE ". $this->_flags);
			$target = clone $this;
			if($target->_video && ($target->_video->_gop===null || $target->_video->_gop==0))
			$target->_video->_gop = VDLConstants::DefaultGOP;
				
			$target->_warnings[VDLConstants::ContainerIndex][] =
				VDLWarnings::ToString(VDLWarnings::ForceCommandline);
		}
		else {
			$target = $this->generateTargetFlavor($source);
			if($target->_video=="" && $target->_audio=="" && $target->_image==""){
				// "Invalid File - No media content";
				$target->_errors[VDLConstants::ContainerIndex][] = VDLErrors::ToString(VDLErrors::NoValidMediaStream);
			}

			if($target->validateTranscoders($source, $target->_transcoders)==false){
				// "No valid transcoder";
				$target->_errors[VDLConstants::ContainerIndex][] = VDLErrors::ToString(VDLErrors::NoValidTranscoders);
			}
//vLog::log("==>\n".print_r($target->_transcoders,true));
		}
		$this->generateCommandLines($target, $target->_transcoders);

		return $target;
	}

	/* ---------------------------
	 * generateCommandLines
	 */
	private function generateCommandLines(VDLFlavor $target, $transcoders){
		foreach($transcoders as $key=>$trPrmObj) {

			if(is_array($trPrmObj)){
				$this->generateOperationSetCommandLines($target, $trPrmObj);
			}
			else{
				$transcoders[$key] = $trPrmObj->GenerateCommandAndConfig($this, $target);
			}
		}
	}
	
	/* ---------------------------
	 * generateOperationSetCommandLines
	 */
	private function generateOperationSetCommandLines(VDLFlavor $target, $transcoders){
VidiunLog::log("==>\n");
		
		$cnt = count($transcoders);
		$i=1;
		foreach($transcoders as $key=>$trPrmObj) {
			$auxTrg = new VDLFlavor();
			$auxTrg = clone $target;
			$transcoders[$key] = $trPrmObj->GenerateCommandAndConfig($this, $auxTrg);
			$i++;
			continue;
		}
	}
		
	/* ---------------------------
	 * ValidateProduct
	 */
	public function ValidateProduct(VDLMediaDataSet $source, VDLFlavor $product)
	{
		VidiunLog::log( ".SRC-->".$source->ToString());
		VidiunLog::log( ".TRG-->".$this->ToString());
		VidiunLog::log( ".PRD-->".$product->ToString());
		
		$rv = $product->ValidateFlavor();

		if($source){
			$srcVid = $source->_video;
			$srcAud = $source->_audio;
			$srcCont = $source->_container;
		}
		else {
			$srcVid = null;
			$srcAud = null;
			$srcCont =null;
		}
		
		/*
		 * ARF (webex) sources don't have proper mediaInfo - thus can not validate the product, skip it
		 * 
		 * - The second portion of the 'if condition' is a workaround to handle invalidly passed inter-src 
		 * asset both as a source and as a product. 
		 * It is 'strstr' rather than 'strcmp', because call to 'product->ValidateFlavor' might add warnings to the ToString
		 */
//		if(isset($srcCont) && $srcCont->GetIdOrFormat()=='arf') {
		if((isset($srcCont) && $srcCont->GetIdOrFormat()=='arf') || strstr($product->ToString(),$source->ToString())!=false) {
			VidiunLog::log("ARF (webex) sources don't have proper mediaInfo - thus can not validate the product");
			return true;
		}
				
		/*
		 * WVM (DRM Widevine) sources don't have proper mediaInfo - thus can not validate the product, skip it
		 */
		if(isset($this->_container) && $this->_container->GetIdOrFormat()=='wvm') {
			VidiunLog::log("WVM (DRM Widevine) sources don't have proper mediaInfo - thus can not validate the product");
			return true;
		}
		
		/*
		 * Evaluate source duration, to be used to check the product duration validity 
		 */
$plannedDur = 0;
		{
			if($this->_clipDur && $this->_clipDur>0){
				$plannedDur = $this->_clipDur;
				$vDur = $plannedDur;
				$aDur = $plannedDur;
				$cDur = $plannedDur;

			}
			else {
				$vDur = isset($srcVid)? $srcVid->_duration: 0;
				$aDur = isset($srcAud)? $srcAud->_duration: 0;
				$cDur = isset($srcCont)? $srcCont->_duration: 0;
				$plannedDur = max(max($aDur, $vDur),$cDur);
			}
		}

		/*
		 * Allow conversion and fixing of invalidly muxed WEB-CAM recordecd files - 
		 * - FLV/Sorenson/Nellimossr
		 * - very HUGE duration
		 * - very LOW bitrate - about several bits-per-sec.
		 * In such cases the 'duration validation' is un-applicable
		 *
		if(isset($srcVid) && $srcVid->IsFormatOf(array("h.263","h263","sorenson spark","vp6")) 
		&& isset($srcAud) && $srcAud->IsFormatOf(array('nellymoser')) && $cDur>0 && isset($srcCont->_fileSize)){
			if($srcCont->_fileSize*8000/$cDur<VDLSanityLimits::MinBitrate) {
				VidiunLog::log("Invalid WEB-CAM source file. Duration validation is un-applicable");
				return true;
			}
		}
		*/
		if($this->_video!==null) {
			if($product->_video===null){
				$product->_errors[VDLConstants::VideoIndex][] = VDLErrors::ToString(VDLErrors::MissingMediaStream);
				$rv=false;
			}
			else {
				$prdVid = $product->_video;
				$trgVid = $this->_video;

					/*
					 *  On short durations, the 'granulariity' of a single frame dur might cause invalidation. 
					 *  Don't check for <2sec
					 */
				if($plannedDur>2000){
					if($prdVid->_duration<$plannedDur*VDLSanityLimits::MinDurationFactor 
					|| $prdVid->_duration>$plannedDur*VDLSanityLimits::MaxDurationFactor) 
					{
						//This check was added to filter out files that have no duration set on their metadata and are of type ogg or ogv to avoid failure on product validation (SUP 546)
						if($aDur==0 && in_array(strtolower($this->_container->GetIdOrFormat()), array("ogg", "ogv")))
						{
							//Do Nothing
						}
						else 
						{
							$product->_errors[VDLConstants::VideoIndex][] = // Invalid product duration
								VDLErrors::ToString(VDLErrors::InvalidDuration, $prdVid->_duration/1000, $plannedDur/1000);
							$rv=false;
						}
					}
					else if($prdVid->_duration<$plannedDur*VDLConstants::ProductDurationFactor) {
						$product->_warnings[VDLConstants::VideoIndex][] =
						VDLWarnings::ToString(VDLWarnings::ProductShortDuration, $prdVid->_duration, $plannedDur);
					}
				}
				
				if(isset($srcVid) && $prdVid->_bitRate<$trgVid->_bitRate*VDLConstants::ProductBitrateFactor) {
					$product->_warnings[VDLConstants::VideoIndex][] = // "Product bitrate too low - ".$prdVid->_bitRate."kbps, required - ".$trgVid->_bitRate."kbps.";
					VDLWarnings::ToString(VDLWarnings::ProductLowBitrate, $prdVid->_bitRate, $srcVid->_bitRate);
				}
			}
		}

		if($this->_audio!==null) {
			if($product->_audio===null){
				$product->_errors[VDLConstants::AudioIndex][] = VDLErrors::ToString(VDLErrors::MissingMediaStream);
				$rv=false;
			}
			else {
				$prdAud = $product->_audio;
				$trgAud = $this->_audio;

					/*
					 * On short durations, the 'granulariity' of a single frame dur might cause invalidation.
					 * Don't check for <2sec
					 */
				if($plannedDur>2000){ 
					if($prdAud->_duration<$plannedDur*VDLSanityLimits::MinDurationFactor 
					|| $prdAud->_duration>$plannedDur*VDLSanityLimits::MaxDurationFactor) {
						$product->_errors[VDLConstants::AudioIndex][] = // Invalid product duration 
						VDLErrors::ToString(VDLErrors::InvalidDuration, $prdAud->_duration/1000, $plannedDur/1000);
						$rv=false;
					}
					else if($prdAud->_duration<$plannedDur*VDLConstants::ProductDurationFactor) {
						$product->_warnings[VDLConstants::AudioIndex][] = // "Product duration too short - ".($prdAud->_duration/1000)."sec, required - ".($srcAud->_duration/1000)."sec.";
						VDLWarnings::ToString(VDLWarnings::ProductShortDuration, $prdAud->_duration, $plannedDur);
					}
				}
				if(isset($srcAud) && $prdAud->_bitRate<$trgAud->_bitRate*VDLConstants::ProductBitrateFactor) {
					$product->_warnings[VDLConstants::AudioIndex][] = // "Product bitrate too low - ".$prdAud->_bitRate."kbps, required - ".$trgAud->_bitRate."kbps.";
					VDLWarnings::ToString(VDLWarnings::ProductLowBitrate, $prdAud->_bitRate, $srcAud->_bitRate);
				}
			}
		}

		if($product->_video===null && $product->_audio===null) {
			// "Invalid File - No media content.";
			$product->_errors[VDLConstants::ContainerIndex][] = VDLErrors::ToString(VDLErrors::NoValidMediaStream);
		}
		VidiunLog::log( ".PRD-->".$product->ToString());

		return $rv;
	}

	/* ------------------------------
	 * IsValid
	 */
	public function IsValid()
	{
		return (count($this->_errors)==0);
	}

	/* ------------------------------
	 * IsRedundant
	 */
	public function IsRedundant()
	{
		return ($this->_flags & VDLFlavor::RedundantFlagBit);
	}

	/* ------------------------------
	 * IsComply
	 */
	public function IsNonComply()
	{
		return ( ($this->_flags & VDLFlavor::BitrateNonComplyFlagBit)
			   ||($this->_flags & VDLFlavor::FrameSizeNonComplyFlagBit)
			   ||($this->_flags & VDLFlavor::MissingContentNonComplyFlagBit));
	}

	/* ------------------------------
	 * IsInArray
	 */
	public function IsInArray(array $arr)
	{
		foreach($arr as $member) {
			if($this->_id==$member->_id) {
				return $member;
			}
		}
		return null;
	}

	/* ---------------------------
	 * EvaluateFileExt
	 */
	public function EvaluateFileExt()
	{
		if($this->_container != null)
		{
			return $this->_container->_id;
		}
		else
		{
			return null;
		}
		
		switch($this->_container->_id){
			case "flv":
			case "avi":
			case "mp4":
			case "mov":
			case "3gp":
			case "ogg":
			case "ogv":
				return $this->_container->_id;
			default:
				return "flv";
		}
	}

	/* ---------------------------
	 * generateTarget
	 */
	private function generateTargetFlavor(VDLMediaDataSet $source) {
		$target = clone $this;
		if($this->_name!=null)
			$target->_name = $this->_name;
		if($this->_container!=""){
			$target->_container = clone $this->_container;
		}

		$sourceDur=0;
			/*
			 * Evaluate source duration
			 */
		if($source) {
			if($source->_video && $source->_video->_duration>0) {
				$sourceDur=$source->_video->_duration;
			}
			else if($source->_audio && $source->_audio->_duration>0) {
				$sourceDur=$source->_audio->_duration;
			}
			else if($source->_container && $source->_container->_duration>0) {
				$sourceDur=$source->_container->_duration;
			}
		}
			/*
			 * Evaluate cliping setting (if any) according to source dur
			 */
		if($sourceDur>0 && $sourceDur<$target->_clipStart+$target->_clipDur) {
			// Mantis 15712 case
			// zeroing the clipDur causes duration validation issues when cliping the end of the file.
//			$target->_clipDur=0;			
			$target->_explicitClipDur=$sourceDur-$target->_clipStart;
		}
		else if(isset($target->_clipStart) && (!isset($target->_clipDur) || $target->_clipDur==0)){
			$target->_explicitClipDur=$sourceDur-$target->_clipStart;
		}
		else
			$target->_explicitClipDur = $target->_clipDur;
			
			/*
			 * Fading needs explicit time limitation on the WM image loop. 
			 * We'll do it with '_explicitClipDur' field.
			 * 
			 * Check each WM data object for multiple-WM mode
			 */
		if(!isset($target->_explicitClipDur) && isset($target->_video) && isset($target->_video->_watermarkData)){
			if(is_array($target->_video->_watermarkData))
				$watermarkDataArr = $target->_video->_watermarkData;
			else
				$watermarkDataArr = array($target->_video->_watermarkData);
			foreach($watermarkDataArr as $watermarkData){
				if(isset($watermarkData->fade)){
					$target->_explicitClipDur = $sourceDur;
					break;
				}
			}
		}

			/*
			 * mpeg2 and theora video formats does not allow reliable 'fastSeekTo' (used on clipping)
			 */
		if($source->_video && $source->_video->IsFormatOf(array("mpeg video","theora"))){
			$target->_fastSeekTo = false;
		}
		else {
			$target->_fastSeekTo = true;
		}

			/*
			 * Disable encryption for sources shorter than 10sec (PLAT-5558)
			 * and IsEncrypt==1
			 * For IsEncrypt==2 ==> encrypt ALL contents
			 */
		if(isset($target->_isEncrypted) && $target->_isEncrypted==1 && $sourceDur<10000) {
			$target->_isEncrypted = 0;
		}
		
		if(isset($source->_decryptionKey)) {
			VidiunLog::log("decryptionKey:".$source->_decryptionKey);
			$target->_decryptionKey = $source->_decryptionKey;
		}
		
			/*
			 * For IMX sources, apply cropping of the top 32 pixs, if the flavor has the ImxCrop flag
			 * 'IMX' ==> mxf/mpeg2 video/ 720x608
			 * Turn off this flag for 'COPY' cases ('cropping' needs transcoding, it does not work for 'copy'
			 */
		if(isset($this->_video)){
			if($this->_video->_isCropIMX==true && $this->_video->_id!=VDLVideoTarget::COPY
			&& isset($source->_container) && $source->_container->IsFormatOf(array("mxf")) 
			&& isset($source->_video) && $source->_video->IsFormatOf(array("mpeg video","mpeg2video")) 
			&& isset($source->_video->_width) && $source->_video->_width==720
			&& isset($source->_video->_height) && ($source->_video->_height==608 || $source->_video->_height==576 || $source->_video->_height==486)){
				$this->_video->_isCropIMX=true;
			}
			else {
				$this->_video->_isCropIMX=false;
			}
			VidiunLog::log('IsCropImx('.$this->_video->_isCropIMX.')');
		}

			/*
			 * For surround - make sure all streams have the same sampleRate
			 */
			$target->_multiStream = self::evaluateTargetAudioMultiStream($source, $target);
	
		if($target->_container->_id==VDLContainerTarget::COPY){
			$target->_container->_id=self::EvaluateCopyContainer($source->_container);
		}

		$target->_container->_duration = $sourceDur;
		$target->_video = null;
		if($this->_video!="") {
			if($source->_video!="" && ($target->_container && !($target->_container->_id==VDLContainerTarget::MP3 || $target->_container->_id==VDLContainerTarget::WMA))){
				/*
				 * Evaluate target video params
				 */
				$target->_video = $this->evaluateTargetVideo($source->_video);
				
					/*
					 * Apply optimization-policy to evaluate 'compliancy' state - 
					 * if not set - use original BitRate oriented optimization
					 */
				if(isset($this->_optimizationPolicy))
					$optimizationPolicy = $this->_optimizationPolicy;
				else
					$optimizationPolicy = VDLOptimizationPolicy::BitrateFlagBit;
				VidiunLog::log('OptimizationPolicy('.$target->_optimizationPolicy.')');
				
					/*
					 * Bitrate oriented optimization -
					 * NonCompliant if the source bitrate significantly lower than the flavor bitrate
					 */
				if($optimizationPolicy & VDLOptimizationPolicy::BitrateFlagBit) {
					if($target->_video->_bitRate<$this->_video->_bitRate*VDLConstants::FlavorBitrateComplianceFactor) {
						$target->_flags = $this->_flags | self::BitrateNonComplyFlagBit;
						$target->_warnings[VDLConstants::VideoIndex][] = 
							VDLWarnings::ToString(VDLWarnings::TargetBitrateNotComply, $target->_video->_bitRate, $this->_video->_bitRate);
					}
				}
					/*
					 * Frame size oriented optimization -
					 * NonCompliant if the source frame size significantly smaller than the flavor frame size
					 */
				if($optimizationPolicy & VDLOptimizationPolicy::FrameSizeFlagBit){
					$srcVid = $source->_video;
					$trgVid = $target->_video;
					$flvrVid= $this->_video;
					$param1=null;
					$param2=null;
					/*
					 * The BitrateCompliance condition prevented some of flavors to be signed as  'Framesize-non-comply'.
					 * Therefore it was removed.
					 */
		//			if(isset($flvrVid->_bitRate) && $flvrVid->_bitRate>0 && isset($srcVid->_bitRate) && $srcVid->_bitRate>0
		//			&& $flvrVid->_bitRate/VDLConstants::FlavorBitrateComplianceFactor<$srcVid->_bitRate) 
					{
						if(isset($flvrVid->_width) && $flvrVid->_width>0 && isset($trgVid->_width) && $trgVid->_width 
						&& $flvrVid->_width>$trgVid->_width/VDLConstants::FlavorFrameSizeComplianceFactor) {
							$target->_flags = $this->_flags | self::FrameSizeNonComplyFlagBit;
							$param1 = "w:$flvrVid->_width";
							$param2 = "w:$trgVid->_width";
						}
						if(isset($flvrVid->_height) && $flvrVid->_height>0 && isset($trgVid->_height) && $trgVid->_height 
						&& $flvrVid->_height>$trgVid->_height/VDLConstants::FlavorFrameSizeComplianceFactor) {
							if(isset($param1)) { $param1.=","; $param2.=","; }
							$param1.= "h:$flvrVid->_height";
							$param2.= "h:$trgVid->_height";
						}
					}
					if(isset($param1)){
						$target->_flags = $this->_flags | self::FrameSizeNonComplyFlagBit;
						$target->_warnings[VDLConstants::VideoIndex][] = 
							VDLWarnings::ToString(VDLWarnings::TargetFrameSizeNotComply, $param1, $param2);
					}
				}
			}
			else if($target->_container && $target->_container->_id==VDLContainerTarget::ISMV) {
					/*
					 * EE cannot generate audio only ISMV, therefore switch to WMA
					 */
				foreach ($this->_transcoders as $trns){
					$rv = strstr($trns->_id,"expressionEncoder.ExpressionEncoder");
					if($rv!=false) {
						$target->_warnings[VDLConstants::ContainerIndex][] = // "The target flavor bitrate {".$target->_video->_bitRate."} does not comply with the requested bitrate (".$this->_video->_bitRate.").";
							VDLWarnings::ToString(VDLWarnings::ChangingFormt, $target->_container->_id, VDLContainerTarget::WMA);
						$target->_container->_id=VDLContainerTarget::WMA;
						break;
					}
				}
			}
		}

		$target->_audio = null;
		if($this->_audio!=""){
			if($source->_audio!=""){
				if (isset($source->_contentStreams)){
					$target->_audio = $this->evaluateTargetAudio($source->_audio, $target, $source->_contentStreams);
				}else{
					$target->_audio = $this->evaluateTargetAudio($source->_audio, $target, null);
				}
				
				/*
				 * When copying AAC from MPEG-TS source into MP4 target, special bitstream filter should be applied 
				 */
				if(isset($source->_container) && $source->_container->IsFormatOf(array("mpeg-ts","mpegts","mts")) 
					&& $source->_audio->IsFormatOf(array("aac")) 
					&& isset($target->_audio) && $target->_audio->IsFormatOf(array(VDLAudioTarget::COPY))
					&& $target->_container->IsFormatOf(array(VDLContainerTarget::MP4))){
					$target->_audio->_aac_adtstoasc_filter = true;
				}
				
				/*
				 * On multi-lingual flavor, 
				 * if required language does not exist in the target, although it is in the flavor(this) - set NonComply flag.
				 * Check both old and new formats of the this::multiStream
				 */
				if((isset($this->_multiStream->audio->languages) && count($this->_multiStream->audio->languages)>0)
				|| VDLAudioMultiStreaming::IsStreamFieldSet($this->_multiStream, "lang")) {
					
					if(!VDLAudioMultiStreaming::IsStreamFieldSet($target->_multiStream,"lang")) {
					$target->_flags = $this->_flags | self::MissingContentNonComplyFlagBit;
					$target->_warnings[VDLConstants::AudioIndex][] = 
						VDLWarnings::ToString(VDLWarnings::MissingMediaStream);
					}
				}
			}
		}
		
		return $target;
	}

	/* ---------------------------
	 * EvaluateCopyContainer
	 */
	public static function EvaluateCopyContainer(VDLContainerData $source)
	{
		$format = VDLContainerTarget::MP4;
//'mp3', 'flash video', 'mpeg audio', 'quicktime', 'mpeg-4','matroska','mpeg video', 'mpeg-ps',
//'mpeg-ts','ogg','wave','webm','windows media','avi','bdav','dv','jpeg','png','mxf','realmedia','shockwave','aiff'
		switch($source->_format){
			case 'mpeg-4':
				$format = VDLContainerTarget::MP4;
				break;
			case 'flash video':
				$format = VDLContainerTarget::FLV;
				break;
			case 'mpeg audio':
			case 'mp3':
				$format = VDLContainerTarget::MP3;
				break;			
			case 'quicktime':
				$format = VDLContainerTarget::MOV;
				break;
			case 'matroska':
				$format = VDLContainerTarget::MKV;
				break;
			case 'mpeg video':
			case 'mpeg-ps':
			case 'mxf':
				$format = VDLContainerTarget::MPEG;
				break;
			case 'mpeg-ts':
			case 'bdav':
				$format = VDLContainerTarget::MPEGTS;
				break;
			case 'ogg':
				$format = VDLContainerTarget::OGG;
				break;
			case 'wave':
				$format = VDLContainerTarget::WAV;
				break;
			case 'webm':
				$format = VDLContainerTarget::WEBM;
				break;
			case 'windows media':
				$format = VDLContainerTarget::WMV;
				break;
			case 'mxf':
				$format = VDLContainerTarget::MXF;
				break;
			case 'avi':
			case 'dv':
			case 'realmedia':
			default:
				$format = VDLContainerTarget::AVI;
				break;
		}
		
		return $format;
	}
	
	/* ---------------------------
	 * evaluateTargetVideo
	 */
	public function evaluateTargetVideo(VDLVideoData $source)
	{
		$targetVid = clone $this->_video;
		$flavorVid = $this->_video;
		$sourceVid = $source;

		if($this->_video->_id=="") {
			switch($this->_container->_id){
				case VDLContainerTarget::FLV:
					$targetVid->_id = VDLVideoTarget::FLV;
					break;
				case VDLContainerTarget::AVI:
					$targetVid->_id = VDLVideoTarget::H264;
					break;
				case VDLContainerTarget::MP4:
				case VDLContainerTarget::M4V:
					$targetVid->_id = VDLVideoTarget::H264;
					break;
				case VDLContainerTarget::MOV:
					$targetVid->_id = VDLVideoTarget::H264;
					break;
				case VDLContainerTarget::_3GP:
					$targetVid->_id = VDLVideoTarget::H264;
					break;
				case VDLContainerTarget::OGG:
				case VDLContainerTarget::OGV:
					$targetVid->_id = VDLVideoTarget::THEORA;
					break;
				case VDLContainerTarget::WMV:
					$targetVid->_id = VDLVideoTarget::WMV2;
					break;
				case VDLContainerTarget::ISMV:
					$targetVid->_id = VDLVideoTarget::WVC1A;
					break;
				case VDLContainerTarget::WEBM:
					$targetVid->_id = VDLVideoTarget::VP8;
					break;
				case VDLContainerTarget::MPEG:
				case VDLContainerTarget::MPEGTS:
				case VDLContainerTarget::M2TS:
				case VDLContainerTarget::APPLEHTTP:
					$targetVid->_id = VDLVideoTarget::H264;
					break;
			}
		}
		
		/*
		 * Evaluate flavor frame-size
		 */
 		$this->evaluateTargetVideoFramesize($sourceVid, $targetVid);

 		/*
		 * Following code is a hack to overcome ffmpeg/x264 AR disorder 
		 * that happens with several hdv source formats
		 */
		$srcVcodec = $source->GetIdOrFormat();
		if(isset($srcVcodec) && in_array($srcVcodec, array("dvh3", "dvhp", "hdv1","hdv2" ,"hdv3", "hdv6"))
//		&& isset($targetVid->_id) && in_array($targetVid->_id, array("h264", "h264b", "h264m","h264h" ))
		&& !($targetVid->_width==0 || $targetVid->_height==0)) {
			$targetVid->_dar = round($targetVid->_width/$targetVid->_height,4);
		}
		
		/*
		 * Evaluate flavor bitrate
		 */
		$this->evaluateTargetVideoBitrate($sourceVid, $targetVid);
		
		/*
		 * Frame Rate - If the flavor fps is zero, evaluate it from the source and
		 * the constants theshold.
		 */
		$this->evaluateTargetVideoFramerate($sourceVid, $targetVid);
		
		/*
		 * COPY does not require following settings
		 */
		if($targetVid->_id==VDLVideoTarget::COPY) {
			$targetVid->_watermarkData = null;
			return $targetVid;
		}
		
		/*
		 * GOP - if gop not set, set it to 2sec according to the required frame rate,
		 * otherwise if gop param is in sec (_isGopInSec) ==> calculate form framerate,
		 * If framerate not set - DefaultGOP(60)
		 */
		if($flavorVid->_gop===null || $flavorVid->_gop==0) {
			if(isset($targetVid->_frameRate)){
				$targetVid->_gop = round(VDLConstants::DefaultGOPinSec*$targetVid->_frameRate);
			}
			else {
				$targetVid->_gop = VDLConstants::DefaultGOP;
			}
		}
		else if(isset($flavorVid->_isGopInSec) && $flavorVid->_isGopInSec>0) {
			if(isset($targetVid->_frameRate)){
				$targetVid->_gop = round($targetVid->_gop*$targetVid->_frameRate);
			}
			else {
				$targetVid->_gop = VDLConstants::DefaultGOP;
			}
		}

			/*
			 * Watermarks, if any ...
			 */
		if(isset($targetVid->_watermarkData)){
				/*
			 	 * Evaluate source frame dims - dar adjustment and rotation
			 	 */
			$targetVid->_watermarkData = self::evaluateTargetWaterMark($sourceVid, $flavorVid, $targetVid->_watermarkData);
		}
		
		$targetVid->_rotation = $sourceVid->_rotation;
		$targetVid->_scanType = $sourceVid->_scanType;
		
		return $targetVid;
	}
	
	/*
	 *	switch frame sizes & inverse display aspect ratio for a certain video.
	 */
	
	private static function invertVideoDimensions(VDLVideoData $video)
	{
		$temp = $video->_height;
		$video->_height = $video->_width;
		$video->_width = $temp;
		if (isset($video->_dar) && $video->_dar != 0)
			$video->_dar = 1/$video->_dar;
	}
	
	/* ---------------------------
	 * evaluateTargetVideoFramesize
	 */
	private function evaluateTargetVideoFramesize(VDLVideoData $source, VDLVideoData $target) 
	{
		$shrinkToSource = $target->_isShrinkFramesizeToSource;
		$invertedVideo = false;
		
		
		/*
		 *	this is for the special case where a source has height > width.
		 *	here it will be inverted & run through the usual flow.
		 *	in this case the source-target frame-sizes ratio after converting should be the same as if the source had a regular height < width.
		 *	boolean flag invertedVideo - for inverting back the source & target later on.
		 */
		if ((isset($source->_dar) && $source->_dar < 1) ||
			(isset($source->_height) && isset($source->_width) && $source->_height > 0 && $source->_width > 0 && $source->_height > $source->_width))
		{
			VidiunLog::debug('inverting source');
			self::invertVideoDimensions($source);
			$invertedVideo = true;
		}
		
		$widSrc = $source->_width;
		$hgtSrc = $source->_height;
		if($widSrc==0 || $hgtSrc==0)
			return;
			
			/*
			 * For IMX - reduce the height by 32 pixs
			 */
		if(isset($target->_isCropIMX) && $target->_isCropIMX==true) {
			$hgtSrc-=32;
		}
		
		$darSrcFrame = $widSrc/$hgtSrc;
		/*
		 * DAR adjustment
		 */
		if($source->_dar!="" && $source->_dar>0){
			$darSrc = $source->_dar;
			$diff = abs(1-$darSrc/$darSrcFrame);
				// Less strict diff (original was diff>0.1) test to allow hadling of 5:4 to 4:3 adjustments
			if($diff>0.05) {  
				$widSrc = $darSrc*$hgtSrc;
				$darSrcFrame = $darSrc;
			}
		}

		/*
		 * Evaluate target frame size, from the source frame size 
		 * and from the predefined target frame size
		 */

			/*
			 * Both target width and height are not set ==> use the source frame size 
			 */
		if(($target->_width==0 || $target->_width=="") && ($target->_height==0 || $target->_height=="")){
			$target->_height = $hgtSrc;
			$target->_width  = $widSrc;
		}
			/*
			 * The target width was net set ==> 
			 * evaluate it from the height while keeping source the aspect ratio 
			 */
		else if($target->_width==0 || $target->_width==""){
			$target->_width = $target->_height*$darSrcFrame;
			if($shrinkToSource && $target->_width>$widSrc) {
				$target->_height = $hgtSrc;
				$target->_width  = $widSrc;
			}
		}
			/*
			 * The target height was net set ==> 
			 * evaluate it from the width while keeping source the aspect ratio 
			 */
		else if($target->_height==0 || $target->_height==""){
			$target->_height = $target->_width/$darSrcFrame;
			if($shrinkToSource && $target->_height>$hgtSrc) {
				$target->_height = $hgtSrc;
				$target->_width  = $widSrc;
			}
		}
			/*
			 * Dual dimension 'keep aspect ratio' mode:
			 * Scale down the source to match inside the flavor params 
			 * predefined frame size while keeping source the aspect ratio
			 */
		else if(isset($target->_arProcessingMode) && $target->_arProcessingMode>0){
			$darTrgFrame = $target->_width/$target->_height;
				/*
				 * AR Mode - Match the both dims frame size & preserve AR mode 
				 */
			if($target->_arProcessingMode==1){
				/*
				 * The target AR is wider than the source
				 */
				if($darTrgFrame>$darSrcFrame){
					$target->_width = $target->_height*$darSrcFrame;
					if($shrinkToSource && $target->_width>$widSrc) {
						$target->_height = $hgtSrc;
						$target->_width  = $widSrc;
					}
				}
				/*
				 * The target AR is narrower than the source
				 */
				else {
					$target->_height = $target->_width/$darSrcFrame;
					if($shrinkToSource && $target->_height>$hgtSrc) {
						$target->_height = $hgtSrc;
						$target->_width  = $widSrc;
					}
				}
			}
				/*
				 * AR Mode - letterboxing
				 */
			else if($target->_arProcessingMode==2){
				/*
				 * The target AR is wider than the source
				 */
				if($shrinkToSource){
					if($darTrgFrame>$darSrcFrame){
						if($target->_height>$hgtSrc) $target->_height = $hgtSrc;
						$target->_width = $target->_height*$darTrgFrame;
					}
					/*
					 * The target AR is narrower than the source
					 */
					else {
						if($target->_width>$widSrc) $target->_width  = $widSrc;
						$target->_height = $target->_width/$darTrgFrame;
					}
				}
			}
				/*
				 * AR Mode - force 16/9 for everything that is not 16:9
				 */
			else if($target->_arProcessingMode==3){
				if($darSrcFrame!=4/3){
					$darTrgFrame = 16/9;
				}
				/*
				 * The target AR is wider than the source
				 */
				if($darTrgFrame>$darSrcFrame){
					$target->_width = $target->_height*$darTrgFrame;
				}
				/*
				 * The target AR is narrower than the source
				 */
				else {
					$target->_height = $target->_width/$darTrgFrame;
				}
				if($shrinkToSource && ($target->_height>$hgtSrc || $target->_width>$widSrc)) {
					$target->_height = $hgtSrc;
					$target->_width  = $widSrc;
				}
			}
		}
			/*
			 * Fixed target frame size
			 */
		else if($shrinkToSource) {
			$darTrg = $target->_width/$target->_height;
			if($target->_height>$hgtSrc) {
				$target->_height=$hgtSrc;
			}
				/*
				 * If the target AR is similar/close (up to 5%) to the src AR,
				 * just trim to the source dims.
				 * Otherwise (src AR != trg AR) - calc the trg wid from trg AR and hgt.
				 */
			if(abs(1-$darTrg/$darSrcFrame)<0.05) {
				if($target->_width>$widSrc) {
					$target->_width=$widSrc;
				}
			}
			else {
				$target->_width = $target->_height*$darTrg;
			}
		}

			/*
			 * AR Mode:5 - force target AR to be as close as possible to source 'intentional' AR
			 * 	The source AR is checked againts 'known' AR (16:9, 4:3, 2.40:1, 2.35:1, ...)
			 * 	if the source AR is 'close enough' to obe of the 'well-known' AR's, the asset is 
			 *	generated with the well-known AR. Otherwise the calculated source AR is taken.
			 *	The asset frame size is adapted to get as close as possible (4 dig percesion) to that AR.
			 *
			 *	This code should reside above, along with other AR processing. But this could lead to 
			 *	behaviour change for some of the customer's that use the ARMode feature.
			 */
		if(isset($target->_arProcessingMode) && $target->_arProcessingMode==5){
			$flvrVid = $this->_video;
			list($w,$h,$d) = self::matchBestAspectRatio(round($widSrc), round($hgtSrc), $flvrVid->_width, $flvrVid->_height);
			if($w!==false) {
				$target->_width = $w;
				$target->_height = $h;
				$target->_dar = $d;
				VidiunLog::log("AR Match: FOUND ($widSrc $hgtSrc) ($flvrVid->_width, $flvrVid->_height) ==> ($w,$h,$d)");
			}
			else {
				$w = round($target->_width);
				$h = round($target->_height);
				$d = $w/$h;
				VidiunLog::log("AR Match: NOT FOUND ($widSrc $hgtSrc) ($flvrVid->_width, $flvrVid->_height) ==> ($w,$h,$d)");
			}
		}

		$target->_height = round($target->_height);
		$target->_width  = round($target->_width);
		
		/*
		 * For anamorphic pixels - set the width to match the required PAR 
		 * and adjsut the target DAR.
		 */
		if(isset($target->_anamorphic) && $target->_anamorphic!=0){
			$dar = $target->_width/$target->_height;
			if(abs($dar-$target->_anamorphic)>0.2) {
				$w=$target->_height*$target->_anamorphic;
				$w = round($w);
				$target->_dar = $dar;
				$target->_width = $w;
			}
		}
		
		/*
		 * x16 - make sure both hgt/wid comply to x16
		 * - if the frame size is an 'industry-standard', skip x16 constraint 
		 * - for h264 targets force MOD 2 for width & height, otherwise x264 crashes.
		 */
		$modVal = 16;
		if((isset($target->_forceMult16) && $target->_forceMult16 == 0)
		|| (($target->_width == 640 || $target->_width == 480) && $target->_height == 360) || ($target->_width == 1920 && $target->_height == 1080)){
			$auxTargets = array(VDLVideoTarget::H264, VDLVideoTarget::H264B, VDLVideoTarget::H264M, VDLVideoTarget::H264H, VDLVideoTarget::H265);
			if(in_array($target->_id, $auxTargets)) {
				$modVal = 2;
		}
		else {
				return;
			}
		}

		self::matchBestModConstrainedVideoFramesize($darSrcFrame, $hgtSrc, $widSrc, $modVal, $target);
		
		/*
		 *      inverting source back for conversion process.
		 *      inverting target back so the output will be inverted as well.
		 */
		if ($invertedVideo)
		{
			VidiunLog::debug('inverting back source & target');
			
			self::invertVideoDimensions($source);
			self::invertVideoDimensions($target);
		}
	}
	
	/* ---------------------------
	 * matchBestModConstrainedVideoFramesize
	 *  The goal is to conform with frame-size 'mod' constraint (mostly mod16) 
	 *  while attempting to match as close as possible the required aspect ratio - 
	 *  - Evaluating the all 4 possible setups (mod-up/mod-down for vid/hgt)
	 *  - Compare each of them to the required AR
	 *  - Find the setup that is closest 
	 */
	protected function matchBestModConstrainedVideoFramesize($darSrcFrame, $hgtSrc, $widSrc, $modVal, VDLVideoData $target) 
	{ 
			/*
			 * Calculate hgt & wid 'mod down' value. If not set - assign 0 
			 */
		$h_dw = ($target->_height>0)? $target->_height - ($target->_height%$modVal): 0;
		$w_dw = ($target->_width>0)? $target->_width - ($target->_width%$modVal): 0;
		
			/*
			 * If 'mod-down' vals equal to original trg val 
			 * ==> leave, further calcs are redundant 
			 * If one of 'mod-down' ==0
			 * ==> assign and leave, further calcs are redundant
			 */
		if($target->_height==$h_dw && $target->_width==$w_dw){
			return;
		}
		else if($h_dw==0 || $w_dw==0){
			$target->_width  = $w_dw;
			$target->_height = $h_dw;
			return;
		}

			/*
			 * Calc 'mod-up' values
			 * Make sure not to exceed the source dims 
			 * and original flavor dims
			 */
		$h_up = $target->_height -($target->_height%$modVal) + $modVal;
		if($h_up>$hgtSrc || ($this->_video->_height>0 && $h_up>$this->_video->_height)) {
			$h_up = $h_dw;
		}
		$w_up = $target->_width  -($target->_width%$modVal) + $modVal;
		if($w_up>$widSrc || ($this->_video->_width>0 && $w_up>$this->_video->_width)) {
			$w_up = $w_dw;
		}
		
			/*
			 * Calc difference between source AR and AR's of various mod-up/down cases.
			 * The target is to find the option that is closest to the source AR.
			 * Array keys notation - 'd' for 'down', 'u' for 'up'
			 */
		$arArr["dd"] = abs($darSrcFrame-$w_dw/$h_dw);
		$arArr["du"] = abs($darSrcFrame-$w_dw/$h_up);
		$arArr["ud"] = abs($darSrcFrame-$w_up/$h_dw);
		$arArr["uu"] = abs($darSrcFrame-$w_up/$h_up);
		
			/*
			 * Sort the array with AR-diffs to find the smallest (closest to source AR)
			 */
		asort($arArr);
		$kAr = key($arArr);
		
			/*
			 * Assign the best match to target dims.
			 */
		switch ($kAr){
		case "dd":
			$target->_width  = $w_dw;
			$target->_height = $h_dw;
			break;
		case "du":
			$target->_width  = $w_dw;
			$target->_height = $h_up;
			break;
		case "ud":
			$target->_width  = $w_up;
			$target->_height = $h_dw;
			break;
		case "uu":
			$target->_width  = $w_up;
			$target->_height = $h_up;
			break;
		}
	}

	/* ---------------------------
	 * matchBestAspectRatio
	 * 	Force target AR to be as close as possible to source 'intentional' AR
	 * 	The source AR is checked againts 'known' AR (16:9, 4:3, 2.40:1, 2.35:1, ...)
	 * 	if the source AR is 'close enough' to obe of the 'well-known' AR's, the asset is 
	 *	generated with the well-known AR. Otherwise the calculated source AR is taken.
	 *	The asset frame size is adapted to get as close as possible - 4 dig percesion, to that AR
	 */
	protected static function matchBestAspectRatio($srcWid, $srcHgt, $assetWid, $assetHgt, $percision=4)
	{
		VidiunLog::log("Input - srcWid:$srcWid,srcHgt:$srcHgt,assetWid:$assetWid,assetHgt:$assetHgt,percision:$percision");

		$dar = null;
	/**/
		$wellKnown = array(4/3,5/4,5/3,16/9,16/8,16/10,2.4,2.39,2.35,1.85);
		foreach($wellKnown as $idx=>$d) {
			if(abs(($srcWid/$srcHgt)-$d)<0.01){
				$dar = round($d,$percision);
				break;
			}
		}
		if(!isset($dar)) {
			$darAux = round($srcWid/$srcHgt,2);
			foreach($wellKnown as $idx=>$d) {
				if(abs($darAux-$d)<0.01){
					$dar = round($d,$percision);
					break;
				}
			}
		}

		if(!isset($dar)) {
			$dar = round($srcWid/$srcHgt,$percision);
		}

		if($assetHgt==0) {
			$assetHgt = round($assetWid/$dar/2)*2;
		}
		if($assetWid==0) {
			$assetWid = round($assetHgt*$dar/2)*2;
		}
		$assetHgt = min($assetHgt,$srcHgt);
		$assetWid = min($assetWid,$srcWid);
		
		if($assetWid>$assetHgt*$dar) {
			$assetWid = round($assetHgt*$dar/2)*2;
		}
		VidiunLog::log("Adjusted - srcWid:$srcWid,srcHgt:$srcHgt,assetWid:$assetWid,assetHgt:$assetHgt");
		
		for($w=round($assetWid/2)*2; $w>=0; $w-=2){
			$h = round($w/$dar);
			if($h==0) break;
			$d = round($w/$h,$percision);
//VidiunLog::log("$w $h $d\n");
			if($d==$dar && $h%2==0) {
				break;
			}
		}

		if($d==$dar) return array($w,$h,$d);
		else return array(false,false,false);
	}
	
	/* ---------------------------
	 * evaluateTargetVideoBitrate
	 * If flavor BR is higher than the source - keep the source BR
	 */
	private static function evaluateTargetVideoBitrate(VDLVideoData $source, VDLVideoData $target) 
	{
		if($target->_isShrinkBitrateToSource!=1) {
			return $target->_bitRate;
		}
		$maxNormalizedBitrate = VDLVideoBitrateNormalize::NormalizeSourceToTarget($source->_id, $source->_bitRate, $target->_id);
			/*
			 * Optional 'contentAwareness' processing, for sources that have 'complexityValue'(bitrate)>500
			 * and the flavor has 'contentAwareness' field set to positive value (maximal gain level),
			 * then use the 'complexityValue' (normalized) instead of the source bitrate as the target upper video limit.
			 */
		if(isset($source->_complexityValue) && $source->_complexityValue>500 
		&& isset($target->_contentAwareness) && $target->_contentAwareness>0 && $target->_contentAwareness<=1) {
			VidiunLog::log("complexityValue($source->_complexityValue),contentAwareness($target->_contentAwareness),targetBR($target->_bitRate)");
			$complexityNormalizedBitrate = VDLVideoBitrateNormalize::NormalizeSourceToTarget(VDLVideoTarget::H264, $source->_complexityValue, $target->_id,1);
			VidiunLog::log("maxNormalizedBitrate($maxNormalizedBitrate),complexityNormalizedBitrate($complexityNormalizedBitrate)");
			/*
			 * Limit the maximal gain (complexityValue vs. target flavor predifined bitrate), to the 'contentAwareness' limit
			 */
			if($complexityNormalizedBitrate < $maxNormalizedBitrate && $complexityNormalizedBitrate < $target->_bitRate){
				$maxGainLimitedBitrate = $target->_bitRate*(1-$target->_contentAwareness);
				if($complexityNormalizedBitrate < $maxGainLimitedBitrate)
					$maxNormalizedBitrate = $maxGainLimitedBitrate;
				else
					$maxNormalizedBitrate = $complexityNormalizedBitrate;
				VidiunLog::log("maxGainLimitedBitrate($maxGainLimitedBitrate), adjsuted maxNormalizedBitrate($maxNormalizedBitrate)");
			}
		}
		
		if($target->_bitRate>$maxNormalizedBitrate){
			$target->_bitRate = $maxNormalizedBitrate;
		}
		return $target->_bitRate = round($target->_bitRate, 0);
	}

	/* ---------------------------
	 * evaluateTargetVideoFramerate
	 */
	private static function evaluateTargetVideoFramerate(VDLVideoData $source, VDLVideoData $target) 
	{
		/*
		 * Frame Rate - If the flavor fps is zero, evaluate it from the source and
		 * the constants theshold.
		 */
		if($target->_frameRate==0) {
			$target->_frameRate = $source->_frameRate;
			if(isset($target->_maxFrameRate) && $target->_maxFrameRate>0)
				$maxFrameRate = $target->_maxFrameRate;
			else
				$maxFrameRate = VDLConstants::MaxFramerate;
			if($target->_frameRate>$maxFrameRate) {
				$target->_warnings[VDLConstants::VideoIndex][] =
					VDLWarnings::ToString(VDLWarnings::TruncatingFramerate, $maxFrameRate, $target->_frameRate);
				/*
				 * On special HFR (High Frane Rate) cases, apply following truncating logic 
				 */
				switch ($target->_frameRate){
					case 47.96:
						$target->_frameRate = 23.98;
						break;
					case 48:
						$target->_frameRate = 24;
						break;
					case 50:
						$target->_frameRate = 25;
						break;
					case 59.94:
						$target->_frameRate = 29.97;
						break;
					case 60:
						$target->_frameRate = 30;
						break;
					default:
						$target->_frameRate = $maxFrameRate;
						break;
				}
			}
			// For webcam/h263 - if FR==0, set FR=24
			else if($target->_frameRate==0 && $source->IsFormatOf(array("h.263","h263","sorenson spark","vp6")) ){
				$target->_frameRate=24;
			}
			// For webrtc recordings - if FR==0, set FR=30
			else if($target->_frameRate==0 && $source->IsFormatOf(array("vp8","v_vp8","vp9","v_vp9","avc","avc1")) ){
				$target->_frameRate=30;
			}
			
			/*
			 * For frame rates to comply w/HLS (relevant for low br 110 and 200 kfps)
			 * 
			 * 110 - either 10 (for ~30fps) or 8 (for 24/25fps)
			 * 200 - for 24 to 30fps, take half of the original targetFR
			 * otherwise - keep the targetFR (the targetFR<24)
			 */
			if($target->_isFrameRateForLowBrAppleHls){
				if($target->_bitRate<=110) {
					$target->_frameRate = $target->_frameRate>=29.97? 10: 8;
				}
				else if($target->_bitRate<=200 && round($target->_frameRate)>=24) {
					$target->_frameRate = round($target->_frameRate/2,2);
				}
			}
		}

		/*
		 * MPEG2 constraint - target fps should be at least 20
		 */
		if($target->_id==VDLVideoTarget::MPEG2){
			$target->_frameRate = max(20,$target->_frameRate);
		}
		
		//Frame rate smaller than 1 causes Memory Fatal Error so in this case set it to 1
		//Changed the setting to force the frame rate from 1 to 5 since we noticed in some cases this causes mp4 h264 assets to played unusually like in a fast forward mode    
		if( $target->_frameRate > 0 &&  $target->_frameRate < 1)
			$target->_frameRate=5;
		
		return $target->_frameRate;
	}

	/**
	 * evaluateTargetWaterMark
	 * Evaluate scale value in case of 'percentage-of-the-source'
	 * Sample 'scale' value "x30%" stands for - 
	 * make the height to be 30% of the source, calculate the width to match the height
	 * 
	 * @param VDLVideoData $target
	 * @param VDLVideoData $target
	 */
	private static function evaluateTargetWaterMark(VDLVideoData $sourceVid, VDLVideoData $flavorVid, $watermarkData) 
	{
		if(!isset($watermarkData)){
			return null;
		}
		
		$srcWid = $srcHgt = $fixImageDar = null;
		if(isset($sourceVid->_width) && isset($sourceVid->_height)){
				/*
				 * On 'fixed/forced-frame-size' mode (flavorVid::_width/flavorVid::_height != 0),
				 * use the calculated 'forced-dar', rather than source::dar.
				 */
			if(isset($flavorVid->_width) && $flavorVid->_width>0 && isset($flavorVid->_height) && $flavorVid->_height>0){
				$dar = $flavorVid->_width/$flavorVid->_height;
				// Handles 'truely' rotated/portrait sources - wid<hgt
				if($sourceVid->_width<$sourceVid->_height)
					$dar = 1/$dar;
			}
			else if(isset($sourceVid->_dar))
				$dar = $sourceVid->_dar;
			else $dar = null;
			$rotation = isset($sourceVid->_rotation)? $sourceVid->_rotation: null;
			list($srcWid, $srcHgt,$fixImageDar) = self::adjustFrameSizeToDarAndRotation(
									$sourceVid->_width, $sourceVid->_height, $dar, $rotation);
		}

		/*
		 * Handle multiple WM settings - WMdata array
		 */
		if(is_array($watermarkData))
			$watermarkDataArr = $watermarkData;
		else
			$watermarkDataArr = array($watermarkData);

		VidiunLog::log("WM objects:".count($watermarkDataArr));

		foreach($watermarkDataArr as $wmI=>$wmData){
			VidiunLog::log("In WM($wmI):".json_encode($wmData));
			if(isset($wmData->scale)){
				$scaleArr = explode("x",$wmData->scale); 
				$widScale = trim($scaleArr[0]); 
				$hgtScale = trim($scaleArr[1]);
				if(strchr($widScale,'%')){
					$widScale = trim($widScale,'%');
					if(isset($srcWid)) {
						$widScale = round($srcWid*$widScale/100);
					}
					else $widScale = 0;
				}
				if(strchr($hgtScale,'%')){
					$hgtScale = trim($hgtScale,'%');
					if(isset($srcHgt)){
						$hgtScale = round($srcHgt*$hgtScale/100);
					}
					else $hgtScale = 0;
				}
				$wmData->scale = "$widScale"."x$hgtScale";
			}

			/*
			 * Apply 'source-proportional-margins' - 
			 * the margins calculated in proportion to source dims 
			 * (similar to same 'scale' functionality)
			 */
			if(isset($wmData->margins)){
				$marginsArr = explode("x",$wmData->margins); 
				$widMargin = trim($marginsArr[0]); 
				$hgtMargin = trim($marginsArr[1]);
				/*
				 * If there is widMargin and it is source-proportional -
				 *  claculate the correct widMargin value
				 */
				if($widMargin!=="") {
					if(strchr($widMargin,'%')){
						$widMargin = trim($widMargin,'%');
						if(isset($srcWid)) {
							if(isset($fixImageDar))
								$widMargin = round($srcWid*$widMargin/(100*$fixImageDar));
							else /**/
								$widMargin = round($srcWid*$widMargin/100);
						}
						else $widMargin = 0;
					}
				}
				/*
				 * If there is hgtMargin and it is source-proportional -
				 *  claculate the correct hgtMargin value
				 */
				if($hgtMargin!=="") {
					if(strchr($hgtMargin,'%')){
						$hgtMargin = trim($hgtMargin,'%');
						if(isset($srcHgt)){
								/*
								 * An attempt to handle 'more' properly rotated ($srcWid>$srcHgt)
								 * and anamorphic ($fixImageDar) cases.
								 * This is not a real solution, but partial improvement
								 */
							if(isset($fixImageDar) && $srcWid>$srcHgt)
								$hgtMargin = round($srcHgt*$hgtMargin*$fixImageDar/(100));
							else /**/
								$hgtMargin = round($srcHgt*$hgtMargin/100);
						}
						else $hgtMargin = 0;
							/*
							 * If the widMargin was not set,
							 *  assign widMargin the hgtMargin value
							 */
						if($widMargin===""){
							$widMargin = $hgtMargin;
						}
					}
				}

					/*
					 * If the hgtMargin was not set and widMargin exiist 
					 *  assign hgtMargin the widMargin value
					 */
				if($hgtMargin==="" && $widMargin!==""){
						if(isset($fixImageDar))
							$hgtMargin = round($widMargin*$fixImageDar);
						else
							$hgtMargin = $widMargin;
				}
				$wmData->margins = "$widMargin"."x$hgtMargin";
				VidiunLog::log("srcWid($srcWid),srcHgt($srcHgt),widMargin($widMargin),hgtMargin($hgtMargin)");
			}

/*
SRC related
[{"imageEntry":"0_eutot9cu","margins":"1.6%x2.8%","scale":"0x10%"},  {"imageEntry":"0_eutot9cu","margins":"-1.6%x2.8%","scale":"0x10%",  "fade":[{"type":"in","start_time":"1","alpha":"1","duration":"0.5"},  {"type":"out","start_time":"6","alpha":"1","duration":"0.5"}]}, {"imageEntry":"0_eutot9cu","margins":"8%x2.8%","scale":"0x10%","fade":[{"type":"in","start_time":"1","alpha":"1","duration":"0.5"},  {"type":"out","start_time":"6","alpha":"1","duration":"0.5"}]}  ]

WM HGT related
[{"imageEntry":"0_eutot9cu","margins":"30%x30%","scale":"0x10%"},  {"imageEntry":"0_eutot9cu","margins":"-30%x30%","scale":"0x10%",  "fade":[{"type":"in","start_time":"1","alpha":"1","duration":"0.5"},  {"type":"out","start_time":"6","alpha":"1","duration":"0.5"}]}, {"imageEntry":"0_eutot9cu","margins":"145%x30%","scale":"0x10%","fade":[{"type":"in","start_time":"1","alpha":"1","duration":"0.5"},  {"type":"out","start_time":"6","alpha":"1","duration":"0.5"}]}  ]
*/

				/*
				 * fixImageDar - to adjust WM dar/dims in case of anamorphic source
				 */
			$wmData->fixImageDar = $fixImageDar;
			$watermarkDataArr[$wmI] = $wmData;
			VidiunLog::log("Final WM($wmI):".json_encode($wmData));
		}
		return $watermarkDataArr;
	}
	
	/**
	 * 
	 * @param unknown_type $width
	 * @param unknown_type $height
	 * @param unknown_type $dar
	 * @param unknown_type $rotation
	 * @return multitype:NULL |multitype:unknown Ambigous <unknown, number>
	 */
	private static function adjustFrameSizeToDarAndRotation($width, $height, $dar, $rotation)
	{
		VidiunLog::log("In: width($width), height($height), dar($dar), rotation($rotation)");
		/*
		 * Evaluate source frame dims - dar adjustment and rotation
		 */
		if(!isset($width) || !isset($height))
			return null;
			
		$adjustedHgt = $height;
		$adjustedWid = $width;
		$fixImageDar = null;
		if(isset($dar)) {
			$aux = round($adjustedHgt*$dar);
			if(abs($aux-$adjustedWid)>10){
				VidiunLog::log("Adjust width($adjustedWid) with dar($dar): $aux");
				$adjustedWid = $aux;
			}
			
			if($width>0 && $height>0){
				/*
				 * fixImageDar - to adjust WM dar/dims in case of anamorphic source - 
				 * 	the image dims should be set into 'opposite direction' in order 
				 *	to allow the anamorphic normalization procideure to restore the correct WM dar/dims
				 *	Applied on;y if the diference to 'normal' dar is >10%
				 */
				$fixImageDar = ($dar*$height)/$width;
				if(abs(1-$fixImageDar)<0.1)
					$fixImageDar = null;
			}
			VidiunLog::log("Adjusted for dar($dar) - ($adjustedWid), height($adjustedHgt),fixImageDar($fixImageDar) ");
		}
		// For 'portrait' sources (rotation -90,90,270) - switch the source dims
		if(isset($rotation) && in_array($rotation, array(-90,90,270))){
				// For fixed-frame-size flavors, call the same func recursively 
			if(isset($fixImageDar) && $fixImageDar!=0 && $dar!=0){
				list($adjustedWid,$adjustedHgt,$fixImageDar) = self::adjustFrameSizeToDarAndRotation($height, $width, 1/$dar, 0);
			}
			else {
				$aux = $adjustedHgt;
				$adjustedHgt = $adjustedWid;
				$adjustedWid = $aux;
				VidiunLog::log("Adjust frame dims to rotation($rotation): width($adjustedWid),height($adjustedHgt)");
			}
		}
		VidiunLog::log("Final: width($adjustedWid),height($adjustedHgt),fixImageDar($fixImageDar)");
		return array($adjustedWid,$adjustedHgt,$fixImageDar);
	}
	
	/* ---------------------------
	 * evaluateTargetAudio
	 */
	public function evaluateTargetAudio(VDLAudioData $source, VDLMediaDataSet $target, $contentStreams=null)
	{
		$targetAud = clone $this->_audio;
		if($targetAud->_id=="" || $targetAud->_id==null) {
			if($target->_container!=null) {
				switch($target->_container->_id){
					case VDLContainerTarget::MP4:
					case VDLContainerTarget::M4V:
					case VDLContainerTarget::_3GP:
						$targetAud->_id=VDLAudioTarget::AAC;
						break;
					case VDLContainerTarget::MP3:
						$targetAud->_id=VDLAudioTarget::MP3;
						break;
					case VDLContainerTarget::OGG:
					case VDLContainerTarget::OGV:
						$targetAud->_id=VDLAudioTarget::VORBIS;
						break;
					case VDLContainerTarget::FLV:
						$targetAud->_id=VDLAudioTarget::MP3;
						break;
					case VDLContainerTarget::WMV:
					case VDLContainerTarget::WMA:
					case VDLContainerTarget::ISMV:
						$targetAud->_id=VDLAudioTarget::WMA;
						break;
					case VDLContainerTarget::WEBM:
					case VDLContainerTarget::MPEGTS:
					case VDLContainerTarget::M2TS:
					case VDLContainerTarget::APPLEHTTP:
						$targetAud->_id=VDLAudioTarget::AAC;
						break;
				};
			}
			else if($target->_video!=null) {
				switch($target->_video->_id){
					case VDLVideoTarget::H264:
					case VDLVideoTarget::H264B:
					case VDLVideoTarget::H264M:
					case VDLVideoTarget::H264H:
						$targetAud->_id=VDLAudioTarget::AAC;
						break;
					case VDLVideoTarget::THEORA:
						$targetAud->_id=VDLAudioTarget::VORBIS;
						break;
					default:
						$targetAud->_id=VDLAudioTarget::MP3;
						break;
				}
			}
			else {
				$targetAud->_id=VDLAudioTarget::MP3;
			}
		}
		elseif ($target->_container->_id==VDLContainerTarget::MP3) {
			switch($targetAud->_id) {
				case VDLVideoTarget::COPY:
					break;
				default:
					$targetAud->_id=VDLAudioTarget::MP3;
					break;
			}
		}

			/*
			 * For MP3 w/out target bitrate - use 64Kb as default
			 */
		if(isset($target->_container) && $target->_container->_id==VDLContainerTarget::MP3
				&& $targetAud->_id==VDLAudioTarget::MP3 && $targetAud->_bitRate==0) {
			$targetAud->_bitRate = 64;
		}
				/* -------------
				 * Adjust target bit depth/resolution if it is set in the source
				 */
		if(!(isset($targetAud->_resolution) && $targetAud->_resolution>0) 
		&& isset($source->_resolution)){
			$targetAud->_resolution=$source->_resolution;
		}
				/* ---------------
				 * Channels (ch):
				 * - AMRNB: ch 1
				 * - MP3: if not defined - set 2, else keep the definition
				 * - else (ch defined): make it minimum between the source ch cnt 
				 * and the required ch cnt
				 */
		if ($targetAud->_id==VDLAudioTarget::AMRNB){
			$targetAud->_channels=1;
		}
		else if($targetAud->_channels==0 
		&& !($targetAud->_id==VDLAudioTarget::AAC || $targetAud->_id==VDLAudioTarget::PCM || $targetAud->_id==VDLAudioTarget::MPEG2)){
			$targetAud->_channels=VDLConstants::DefaultAudioChannels;
		}
		else {
			/*
			 * Adjust source channnels count to match the mapping settings
			 * The evaluated multiStream::olayout overrides the flavor::audioChannel setting
			 */
			$multiStreamChannels = 0;
			if(isset($target->_multiStream->audio)) {
				$multiStreamChannels = $target->_multiStream->audio->getAudioChannels();
			}
			if($multiStreamChannels>0){
				$targetAud->_channels = $multiStreamChannels;
			}
			else {
				$targetAud->_channels = min($targetAud->_channels, $source->_channels);
			}
		}
				/* ----------------
				 * Normalize sample rate - 
				 * - FLV/MP3: on auto get sr from the source. Follow the spec valid values - 11025,22050,44100.
				 * - AMRNB - br <=12.2, sr <= 8000
				 * - AAC or MP3/non flv: on auto use source sr (if available). Truncate to valid range(11025-48000) 
				 */
		if(($target->_container!=null && $target->_container->_id==VDLContainerTarget::FLV) 
		 && $targetAud->_id==VDLAudioTarget::MP3) {
			if($targetAud->_sampleRate==0 && $source->_sampleRate && $source->_sampleRate>0){
				$targetAud->_sampleRate=$source->_sampleRate;
			}
			$trgSr = $targetAud->_sampleRate;
			if($targetAud->_sampleRate>44100)
				$trgSr=44100;
			if($targetAud->_sampleRate<44100)
				$trgSr=22050;
			if($targetAud->_sampleRate<22050)
				$trgSr=11025;
			$targetAud->_sampleRate = $trgSr;
		}
		else if($targetAud->_id==VDLAudioTarget::AMRNB) { 
			if ($targetAud->_sampleRate==0 || $targetAud->_sampleRate>8000)
				$targetAud->_sampleRate=8000;
			if ($targetAud->_bitRate==0 || $targetAud->_bitRate>12.2)
				$targetAud->_bitRate=12.2;
		}
		else {
			if($targetAud->_sampleRate==0){
				/*
				 * AAC targets should get default 44.1, rather than source SR
				 */
				if($source->_sampleRate>0 && $targetAud->_id!=VDLAudioTarget::AAC) {
					$targetAud->_sampleRate=max(VDLConstants::MinAudioSampleRate,min(VDLConstants::MaxAudioSampleRate,$source->_sampleRate));
				}
				else {
					$targetAud->_sampleRate=VDLConstants::DefaultAudioSampleRate;
				}
			}
			else {
				$targetAud->_sampleRate=max(VDLConstants::MinAudioSampleRate,min(VDLConstants::MaxAudioSampleRate,$targetAud->_sampleRate));
			}
		}
		
			/*
			 * For following cases the audio should be resampled with ffmpeg 'aresample' filter
			 * - Nellimoser audio source
			 * - Low sample-rate audio (<16000hz)
			 * - target other than OGG/Vorbis
			 * DO-NOT try to resample on 'copy' cases - it can not be done
			 */
		if(!$target->_container->IsFormatOf(array(VDLContainerTarget::OGG,VDLContainerTarget::OGV))
		&& !$targetAud->IsFormatOf(array(VDLAudioTarget::COPY))
		&& ($source->IsFormatOf(array('nellymoser'))||($source->_sampleRate && $source->_sampleRate>0 && $source->_sampleRate<16000))) {
			$targetAud->_useResampleFilter = true;
		}
			/*
			 * Check for 'downmix' audio, it requires special ffmpeg processing 
			 */
		if(!$targetAud->IsFormatOf(array(VDLAudioTarget::COPY))
		&& isset($contentStreams) && isset($contentStreams->audio) && count($contentStreams->audio)==1 
		&& isset($contentStreams->audio[0]->audioChannelLayout)
		&& $contentStreams->audio[0]->audioChannelLayout==VDLAudioLayouts::DOWNMIX){
			$target->_multiStream = new stdClass();
			$target->_multiStream->audio = new VDLAudioMultiStreamingHelper(json_decode('{"streams":[{"mapping":[2]}]}'));
			$stream = new VDLStreamDescriptor(array($contentStreams->audio[0]->id));
			$target->_multiStream->audio = new VDLAudioMultiStreamingHelper();
			$target->_multiStream->audio->addStream($stream);
			$target->_multiStream->audio->streams[0]->downmix = 1;
		}

		return $targetAud;
	}

	/**
	 * 
	 * @param unknown_type $source
	 * @param unknown_type $target
	 * @return NULL|stdClass
	 */
	private static function evaluateTargetAudioMultiStream($source, $target) 
	{
		/*
		 * Analyse the source to determine whether it contains multi-stream audio.
		* In case it does and the flavor has 'multiStream' set to 'auto-detect' (default action) -
		* try to define a multiStream processing setup
		*
		* NEW for SURROUND!!!!
		* Struct:
		* 	detect (optional) - 'auto', when set all other fields are omitted, 
		* 	audio - either as a single field or as array
		* 		languages - array of required languages ("eng","esp")
		* 		streams - array of source stream ids to map-in (see bellow).
		* 		action	(optional) - 'merge' (default),'separate'. When set to 'separate' 'olayout' ignored
		* 		olayout	(optional) - output layout - represents the surround layout, not necessarily the number of audioChannels
		* 	video
		* 		...
		*
		* Use cases -
		* Multi-lingual settings (current behavior)
		* 	Check whether the source contains the requested language(s)
		* 	Generate if it does.
		* 
		* NO Surround settings (current behavior)
		* 	Check whether the source has minimal detectable surround streams 
		* 	(or downmix or FL,FR and mono/FC)
		* 	If it does - use one of those to downmix to stereo.
		* 	Otherwise - fallback to ffmpeg default ("take what u can")
		* 
		* Surround settings (streams and olayout)
		* 	Filter-in the 'streams' that actually exist in the source.
		* 	Check whether filtered in streams match the requested output 
		* 	layout definition (5,5.1,7.1,...).
		* 	If the source contains surround stream (single multi-channel stream, for example 7.1)  
		* 	If the source contains separate audio streams, attempt to merge them into required multiSetting::olayout - 
		* 		if there are detectable surround streams (based on per stream 'audioLayout' notation-FL,FR,FC,...) - use them, 
		* 		otherwise try to match enough 'undetected' streams (for 5.1 - take first 6 available streams.
		* 		otherwise - fallback to ffmpeg default "take what u can" (usually the first stream).
		* 	The matching streams are mapped-in (-map 0:2 ...).
		* 	
		* Surround settings - only 'streams' are set
		* 	Default output audio layout - stereo
		* 
		* Surround settings - only output layout ('olayout') is set
		* 	Try to map-in ALL audio sets
		* 
		* 'Mapping-in' -
		* 	There is source audio stream that has the stream id that listed in the 
		* 	multiSetting::streams array.
		* 	For surround - the filtered-in streams must have the same format,duration,
		* 	sampleRate,number of channels.
		*
		* Sample json string:
		* 		- {"detect":"auto"}
		* 		- {"audio":{"languages":["eng","esp"]}}
		* 		- {"audio":{"streams":[1,2]}}
		* 		- {"audio":{"streams":[1,2,3],"olayout":2}}
		* 		-- Filter-in audio streams 1,2,3 and merge into stereo
		* 		- {"audio":{"streams":["all"],"olayout":2}}
		* 		-- Filter-in ALL audio streams and merge into stereo
		* 		- {"audio":{"streams":["1","2","3","14"],"action":"separate"},"detect":"auto"}
		* 		- 
		* 		-- 'action:separate' to keep separate streams
		* 		- {"audio":{"streams":["1","2","3","14"],"olayout":5.1},"detect":"auto"}
		* 		- {"audio":{"streams":["all"],"olayout":5.1},"detect":"auto"}
		* 		- {"audio":{"olayout":5.1},"detect":"auto"}
		* 		-- If 'olayout' is set and 'streams' ommitted - assume 'streams:all'
		*/
		
		if (!isset($source->_contentStreams->audio))
			return null;

		$setupMultiStream = isset($target->_multiStream->audio)? $target->_multiStream->audio: null;
		/*
		 * For audio COPY cases there should be no 'default' multiStream processing (when the setupMultiStream is not set)
		 */
		if(!isset($setupMultiStream) && isset($target->_audio) && $target->_audio->IsFormatOf(array(VDLAudioTarget::COPY))){
			return null;
		}
		
		$overrideStreams   = isset($target->_multiStream->source)? $target->_multiStream->source: null;
			/*
			 * The 'default' flow - 
			 * Check analyze results for
			 * - 'streamsAsChannels' - process them as sorround streams
			 * - 'languages - process them as multi-lingual
			 * - otherwise remove the 'multiStream' object'
			 */
		$multiStreamHelper = new VDLAudioMultiStreamingHelper($setupMultiStream);
		$audioStreams = $multiStreamHelper->GetSettings($source->_contentStreams, $overrideStreams);
		if(isset($audioStreams)){
			$targetMultiStream = new stdClass();
			$targetMultiStream->audio = $audioStreams;
			return $targetMultiStream;
		}
		else 
			return null;
	}

	/* ---------------------------
	 * validateTranscoders
	 * - Remove the engines that in the blacklist for that codec/format/etc
	 */
	private function validateTranscoders(VDLMediaDataSet $source, &$transcoders, $inSet=false)
	{
VidiunLog::log("==>\n");
		$cnt = count($transcoders);
		$i = 0;
		foreach($transcoders as $key=>$trPrm) {
			if(is_array($trPrm)){
				$cnt = count($trPrm);
				$this->validateTranscoders($source, $trPrm, true);
				if($cnt>count($trPrm)){
					unset($transcoders[$key]);
					$this->_warnings[VDLConstants::ContainerIndex][] = 
						VDLWarnings::ToString(VDLWarnings::RemovingMultilineTranscoding);
				}
			}
			else {
				if(is_null($transcoders[$key]->_engine)){
					$this->_warnings[VDLConstants::ContainerIndex][] = //"The transcoder (".$key.") can not process the (".$sourcePart->_id."/".$sourcePart->_format. ").";
								VDLWarnings::ToString(VDLWarnings::MissingTranscoderEngine, $transcoders[$key]->_id);
					unset($transcoders[$key]);
				}
				else {
					if($inSet){		
						VidiunLog::log(": inSet,cnt:$cnt,i:$i");
						if($i>0){
							$transcoders[$key]->_engine->set_sourceBlacklist(null);
						}
						if($i<$cnt-1){
							$transcoders[$key]->_engine->set_targetBlackList(null);
						}
					}
					$rv=$transcoders[$key]->_engine->CheckConstraints($source, $this, $this->_errors, $this->_warnings);
					if($rv==true){
						unset($transcoders[$key]);
					}
				}
			}
			$i++;
		}
		if(count($transcoders)){
			return true;
		}
		return false;
	}
	
	/* ---------------------------
	 * Blacklist processing
	 */
	private static function checkBlackList($blackList, $transcoder, $mediaSet)
	{
		if(array_key_exists($transcoder, $blackList)) {
			foreach ($blackList[$transcoder] as $keyPart => $subBlackList){
				$sourcePart = null;
				switch($keyPart){
				case VDLConstants::ContainerIndex;
					$sourcePart = $mediaSet->_container;
					break;
				case VDLConstants::VideoIndex;
					$sourcePart = $mediaSet->_video;
					break;
				case VDLConstants::AudioIndex;
					$sourcePart = $mediaSet->_audio;
					break;
				default:
					continue;
				}
				if($sourcePart && is_array($subBlackList)
				&& (in_array($sourcePart->_id, $subBlackList)
				|| in_array($sourcePart->_format, $subBlackList))) {
					return $sourcePart;
				}
			}
		}
		return null;
	}
}

#if 0
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
/* ===========================
 * VDLFlavor2Tags
 */
class VDLFlavor2Tags {
	static $ItunesFormats = array("mpeg-4","mpeg audio", "aiff", "wave");
	static $FlashFormats = array("flash video", "flv", "f4v","flash","flashvideo");
	static $FlashPlayableFormats = array("mpeg-4","mpeg audio");
	static $H264Synonyms = array("avc","avc1","h264","h.264");
	static $MP4ContainerSynonyms = array("mpeg-4", "mp4");

	/* ---------------------------
	 * ToTags
	 */

	public static function ToTags(VDLMediaDataSet $source, $tagsToCheck=null)
	{
		//		$aaa=VDLFlavor2Tags::$ItunesFormats;
		$tagsIn = array();
		$tagsOut = array();
		$flavor=null;
		if(is_array($tagsToCheck)) {
			if($tagsToCheck[0] instanceof VDLFlavor) {
				foreach($tagsToCheck as $tagToCheck) {
					$tagsOut = $tagsOut + VDLFlavor2Tags::ToTags($source, $tagToCheck);
					return $tagsOut;
				}
			}
			else {
				$tagsIn = $tagsToCheck;
			}
		}
		else if($tagsToCheck instanceof VDLFlavor) {
			$flavor = $tagsToCheck;
			if(is_array($tagsToCheck->_tags))
			$tagsIn = $tagsToCheck->_tags;
			else
			$tagsIn[0] = $tagsToCheck->_tags;
		}
		else {
			$tagsIn[0] = $tagsToCheck;
		}

		foreach($tagsIn as $tag) {
			switch($tag){
				case "web":
					if($source->_container->IsFormatOf(VDLFlavor2Tags::$FlashFormats))
					$tagsOut[] = $tag;
					else if(VDLFlavor2Tags::isMp4($source))
					$tagsOut[] = $tag;
					else if(VDLFlavor2Tags::isMpegAudio($source))
					$tagsOut[] = $tag;
					/*
					 else {
						if($source->_container->IsFormatOf(VDLFlavor2Tags::$FlashPlayableFormats)) {
						$audFormats = array("mpeg audio");
						if(($source->_video && $source->_video->IsFormatOf(VDLFlavor2Tags::$H264Synonyms))
						|| ($source->_audio && $source->_audio->IsFormatOf($audFormats))){
						$tagsOut[] = $tag;
						}
						}
						}
						*/
					break;
				case "itunes":
					if($source->_container->_id=="qt"
					|| $source->_container->IsFormatOf(VDLFlavor2Tags::$ItunesFormats))
					$tagsOut[] = $tag;
					break;
				case "mbr":
					if($flavor!=null && VDLFlavor2Tags::isMbr($source, $flavor))
					$tagsOut[] = $tag;
					break;
				default:
					break;
			}
		}

		return $tagsOut;
	}

	/* ---------------------------
	 * isMbr
	 */
	private static function isMbr(VDLMediaDataSet $source, VDLFlavor $flavor)
	{
		if($source->_container->IsFormatOf(VDLFlavor2Tags::$FlashFormats)
		&& $flavor->_container->IsFormatOf(VDLFlavor2Tags::$FlashFormats)) {
			;
		}
		else
		if(VDLFlavor2Tags::isMp4($source) && VDLFlavor2Tags::isMp4($flavor)) {
			;
		}
		else {
			return false;
		}
		/*

		if(!(($source->_container->IsFormatOf(VDLFlavor2Tags::$FlashFormats) && $flavor->_container->IsFormatOf(VDLFlavor2Tags::$FlashFormats))
		|| ($source->_container->IsFormatOf(array("mpeg-4")) && $source->_video->IsFormatOf(VDLFlavor2Tags::$H264Synonyms))
		) ) )
		return false;
		*/
		return true;
		return false;
	}

	/* ---------------------------
	 * isMp4
	 */
	private static function isMp4(VDLMediaDataSet $media, $doVideoCheck=true)
	{
		if($media->_container->IsFormatOf(VDLFlavor2Tags::$MP4ContainerSynonyms)
		&&($media->_video==null || $media->_video->IsFormatOf(VDLFlavor2Tags::$H264Synonyms))
		&&($media->_audio==null || $media->_audio->IsFormatOf(array("mpeg audio", "mp3","aac")))
		){
			return true;
		}
		return false;
	}

	/* ---------------------------
	 * isMpegAudio
	 */
	private static function isMpegAudio(VDLMediaDataSet $media)
	{
		if($media->_container->IsFormatOf(array("mpeg audio", "mp3"))
		&& $media->_video!=null
		&& $media->_video->IsFormatOf(array("mpeg audio", "mp3"))){
			return true;
		}
		return false;
	}

}
#endif

?>

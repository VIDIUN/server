<?php
//include_once("StringTokenizer.php");
//include_once("VDLMediaDataSet.php");
//include_once 'VDLUtils.php';

	/* ---------------------------
	 * VDLMediaInfoLoader
	 */
	class VDLMediaInfoLoader extends StringTokenizer {
		public function __construct(/*string*/ $str) {
			parent::__construct($str, "\t\n");
		}
		
		public function __destruct() {
		}

		/* .........................
		 * Load
		 */ 
		function Load(&$dataSet) {
			$fieldCnt=0;
			$streamsCnt = 0;
			$streamsColStr = null;
			$section = "general";
			$sectionID = 0;
			while ($this->hasMoreTokens()) {
				$tok = strtolower(trim($this->nextToken()));
				if(strrpos($tok, ":") == false){
					$sectionID = strchr($tok,"#");
					if($sectionID) {
						$sectionID = trim($sectionID,"#"); 
					}
					else
						$sectionID = 0;

					if(strstr($tok,"general")==true)
						$section = "general";
					else if(strstr($tok,VDLConstants::VideoIndex)==true)
						$section = VDLConstants::VideoIndex;
					else if(strstr($tok,VDLConstants::AudioIndex)==true)
						$section = VDLConstants::AudioIndex;
					else if(strstr($tok,VDLConstants::ImageIndex)==true)
						$section = VDLConstants::ImageIndex;
					else	
						$section = $tok;
					$streamsCnt++;
					if($streamsColStr===null)
						$streamsColStr = $tok;
					else
						$streamsColStr = $streamsColStr.",".$tok;
				}
				else if($sectionID<=1) {
					$key =  trim(substr($tok, 0, strpos($tok, ":")) );
					$val =  trim(substr(strstr($tok, ":"),1));
					switch($section) {
					case "general":
						$this->loadContainerSet($dataSet->_container, $key, $val);
						break;
					case VDLConstants::VideoIndex:
						$this->loadVideoSet($dataSet->_video, $key, $val);
						break;
					case VDLConstants::ImageIndex:
						$this->loadVideoSet($dataSet->_image, $key, $val);
						break;
					case VDLConstants::AudioIndex:
						$this->loadAudioSet($dataSet->_audio, $key, $val);
						break;
					}
					$fieldCnt++;
				}
			}
			if($dataSet->_container!=null){
				$streamsColStr = "1+".$streamsCnt.":".$streamsColStr;
			}
			else
				$streamsColStr = "0+".$streamsCnt.":".$streamsColStr;
//			$dataSet->_multiStream = $streamsColStr;
//			VidiunLog::info("StreamsColStr- ".$dataSet->_multiStream);
		}

		/* ------------------------------
		 * loadAudioSet
		 */
		private function loadAudioSet(&$audioData, $key, $val) {
			if($audioData=="")
				$audioData = new VDLAudioData();
			switch($key) {
			case "channel(s)":
				$audioData->_channels = VDLUtils::trima($val);
				settype($audioData->_channels, "integer");
				break;
			case "sampling rate":
				$audioData->_sampleRate = VDLUtils::trima($val);
				settype($audioData->_sampleRate, "float");
				if($audioData->_sampleRate<1000)
					$audioData->_sampleRate *= 1000;
				break;
			case "resolution":
				$audioData->_resolution = VDLUtils::trima($val);
				settype($audioData->_resolution, "integer");
				break;
			default:
				$this->loadBaseSet($audioData, $key, $val);
				break;
			}
		}

		/* .........................
		 * loadVideoSet
		 */
		private function loadVideoSet(&$videoData, $key, $val) {
			if($videoData=="")
				$videoData = new VDLVideoData();
			switch($key) {
			case "width":
				$videoData->_width = VDLUtils::trima($val);
				settype($videoData->_width, "integer");
				break;
			case "height":
				$videoData->_height = VDLUtils::trima($val);
				settype($videoData->_height, "integer");
				break;
			case "frame rate":
				$videoData->_frameRate = VDLUtils::trima($val);
				settype($videoData->_frameRate, "float");
				break;
			case "nominal frame rate":
				if(!isset($videoData->_frameRate)){
					$videoData->_frameRate = VDLUtils::trima($val);
					settype($videoData->_frameRate, "float");
				}
				break;
			case "display aspect ratio":
				$val = VDLUtils::trima($val);
				if(strstr($val, ":")==true){
					$darW = trim(substr($val, 0, strpos($val, ":")) );
					$darH = trim(substr(strstr($val, ":"),1));
					if($darH>0)
						$videoData->_dar = $darW/$darH;
					else
						$videoData->_dar = null;
					
				}
				else if(strstr($val, "/")==true){
					$darW = trim(substr($val, 0, strpos($val, "/")));
					$darH = trim(substr(strstr($val, "/"),1));
					if($darW>0)
						$videoData->_dar = $darW/$darH;
					else
						$videoData->_dar = null;
				}
				else if($val) {
					$videoData->_dar = (float)$val;
				}
/*
				$val = $this->trima($val);
				if(strstr($val, ":")==true){
					$darW = trim(substr($val, 0, strpos($val, ":")));
					$darH = trim(substr(strstr($val, ":"),1));
					if($darW>0)
						$mediaInfo->videoDar = $darW / $darH;
					else
						$mediaInfo->videoDar = null;
				}
				else if(strstr($val, "/")==true){
					$darW = trim(substr($val, 0, strpos($val, "/")));
					$darH = trim(substr(strstr($val, "/"),1));
					if($darW>0)
						$mediaInfo->videoDar = $darW / $darH;
					else
						$mediaInfo->videoDar = null;
				}
				else if($val) {
					$mediaInfo->videoDar = (float)$val;
				}
				break;

 */
				break;
			case "rotation":
				$videoData->_rotation = VDLUtils::trima($val);
				settype($videoData->_rotation, "integer");
				break;
			case "scan type":
				$scanType = VDLUtils::trima($val);
				if($scanType!="progressive") {
					$videoData->_scanType=1;
				}
				else {
					$videoData->_scanType=0;
				}
//				settype($videoData->_rotation, "integer");
				break;
			default:
				$this->loadBaseSet($videoData, $key, $val);
				break;
			}
		}

		/* .........................
		 * loadContainerSet
		 */
		private function loadContainerSet(&$containerData, $key, $val) {
			if($containerData=="")
				$containerData = new VDLContainerData();
			switch($key) {
			case "file size":
				$containerData->_fileSize = VDLUtils::convertValue2kbits(VDLUtils::trima($val));
				break;
			case "complete name":
				$containerData->_fileName = $val;
				break;
			default:
				$this->loadBaseSet($containerData, $key, $val);
				break;
			}
		}
		
		// .........................
		// loadBaseSet
		//
		private function loadBaseSet(&$baseData, $key, $val) 
		{
			switch($key) {
			case "codec id":
				$baseData->_id = $val;
				break;
			case "format":
				$baseData->_format = $val;
				break;
			case "duration":
				$baseData->_duration = VDLUtils::convertDuration2msec($val);
				break;
			case "bit rate":
				$baseData->_bitRate = VDLUtils::convertValue2kbits(VDLUtils::trima($val));
				break;
			default:
	//echo "<br>". "key=". $key . " val=" . $val . "<br>";
				$baseData->_params[$key] = $val;
				break;
			}
		}
	
	}

?>
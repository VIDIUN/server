<?php
/**
 * @package Scheduler
 * @subpackage Conversion.engines
 */
class VConversionEngineChunkedFfmpeg  extends VConversionEngineFfmpeg
{
	const CHUNKED_FFMPEG = "chunked_ffmpeg";
	
	public function getName()
	{
		return self::CHUNKED_FFMPEG;
	}
	
	public function getType()
	{
		return VidiunConversionEngineType::CHUNKED_FFMPEG;
	}
	
	public function getCmd ()
	{
		return VBatchBase::$taskConfig->params->ffmpegCmd;
	}
	
	/**
	 * execute_conversion_cmdline
	 *	Chunked Encoding can executed both in standalone and memcache managed modes.
	 *	'executionMode' config field used to differntiate between the modes, 
	 *	allowed values - 'standalone'/'memcache'
	 */
	protected function execute_conversion_cmdline($command, &$returnVar)
	{
		VidiunLog::log($command);
		if(strstr($command,"ffmpeg")===false)
			return parent::execute_conversion_cmdline($command, $returnVar);
		if(!isset(VBatchBase::$taskConfig->params->executionMode)){
			$returnVar = -1;
			$errMsg = "ERROR: Missing executionMode value in the batch/worker.ini";
			VidiunLog::log($errMsg);
			return ($errMsg);
		}
		
		$executionMode = VBatchBase::$taskConfig->params->executionMode;
		if($executionMode=="standalone") {
			$output=$this->execute_chunked_encode_standalone($command, $returnVar);
		}
		else if($executionMode=="memcache"){
			$output=$this->execute_chunked_encode_memcache($command, $returnVar);
		}
		else {
			$returnVar = -1;
			$errMsg = "ERROR: Invalid executionMode value ($executionMode) in the batch/worker.ini";
			VidiunLog::log($errMsg);
			return ($errMsg);
		}
		VidiunLog::log("rv($returnVar),".print_r($output,1));
		return $output;
	}

	/**
	 * execute_chunked_encode_memcache
	 * 	Execute memcache based (distributed) Chunked Encode session
	 *	Uses following configuration fields - 
	 *	- chunkedEncodeMemcacheHost - memcache host URL (mandatory)
	 *	- chunkedEncodeMemcachePort - memcache host port (mandatory)
	 *	- chunkedEncodeMemcacheToken - token to differentiate between general/global Vidiun jobs and per customer dedicated servers (optional, default:null)
	 *	- chunkedEncodeMaxConcurrent - maximum concurrently executed chunks jobs, more or less servers core number (optional, default:5)
	 */
	protected function execute_chunked_encode_memcache($cmdLine, &$returnVar)
	{
		VidiunLog::log("Original cmdLine:$cmdLine");
		
				/*
				 * 'chunkedEncodeMemcacheHost' and 'chunkedEncodeMemcachePort'
				 * are mandatory
				 */
		if(!(isset(VBatchBase::$taskConfig->params->chunkedEncodeMemcacheHost) 
		&& isset(VBatchBase::$taskConfig->params->chunkedEncodeMemcachePort))){
			$returnVar = -1;
			$errMsg = "ERROR: Missing memcache host/port in the batch/worker.ini";
			VidiunLog::log($errMsg);
			return ($errMsg);
		}
			/*
			 * Clean up the cmd line - remove 'ffmpeg' and log file redirection instructions
			 * those will be handled by the Chunked flow
			 */
		$cmdLineAdjusted = $this->adjust_cmdline($cmdLine);
		
		{
			$host = VBatchBase::$taskConfig->params->chunkedEncodeMemcacheHost;
			$port = VBatchBase::$taskConfig->params->chunkedEncodeMemcachePort;
			
			if(isset(VBatchBase::$taskConfig->params->chunkedEncodeMemcacheToken)){
				$token = VBatchBase::$taskConfig->params->chunkedEncodeMemcacheToken;
			}
			else $token = null;
			
			if(isset(VBatchBase::$taskConfig->params->chunkedEncodeMaxConcurrent)){
				$concurrent = VBatchBase::$taskConfig->params->chunkedEncodeMaxConcurrent;
			}
			else 
				$concurrent = 5;

			$sessionName = null;
		}
		{
			$cmdLine = 'php -r "';
			$cmdLine.= 'require_once \'/opt/vidiun/app/batch/bootstrap.php\';';

			$cmdLine.= '\$rv=VChunkedEncodeMemcacheWrap::ExecuteSession(';
			$cmdLine.= '\''.($host).'\',';
			$cmdLine.= '\''.($port).'\',';
			$cmdLine.= '\''.($token).'\',';
			$cmdLine.= '\''.($concurrent).'\',';
			$cmdLine.= '\''.($sessionName).'\',';
			$cmdLine.= '\''.$cmdLineAdjusted.'\');';
			$cmdLine.= 'if(\$rv==false) exit(1);';
			$cmdLine.= '"';
		}
		$cmdLine.= " >> ".$this->logFilePath." 2>&1";
		VidiunLog::log("Final cmdLine:$cmdLine");

		$output = system($cmdLine, $returnVar);
		VidiunLog::log("rv($returnVar),".print_r($output,1));
		return $output;
	}
	
	/**
	 * execute_chunked_encode_standalone
	 * 	Execute standalone (one server) Chunked Encode session
	 *	Uses following configuration fields - 
	 *	- chunkedEncodeMaxConcurrent - maximum concurrently executed chunks jobs, more or less servers core number (optional, default:5)
	 */
	protected function execute_chunked_encode_standalone($cmdLine, &$returnVar)
	{
		VidiunLog::log("Original cmdLine:$cmdLine");
			/*
			 * Clean up the cmd line - remove 'ffmpeg' and log file redirection instructions
			 * those will be handled by the Chunked flow
			 */
		$cmdLineAdjusted = $this->adjust_cmdline($cmdLine);

		{
			if(isset(VBatchBase::$taskConfig->params->chunkedEncodeMaxConcurrent)){
				$concurrent = VBatchBase::$taskConfig->params->chunkedEncodeMaxConcurrent;
			}
			else
				$concurrent = 5;

			if(isset(VBatchBase::$taskConfig->params->chunkedEncodeMinConcurrent)) {
				$concurrentMin = VBatchBase::$taskConfig->params->chunkedEncodeMinConcurrent;
			}
			else
				$concurrentMin = 1;
			$sessionName = null;
			
			$cmdLine = 'php -r "';
			$cmdLine.= 'require_once \'/opt/vidiun/app/batch/bootstrap.php\';';
			
			$cmdLine.= '\$rv=VChunkedEncodeSessionManagerStandalone::ExecuteSession(';
			$cmdLine.= '\''.($concurrent).'\',';
			$cmdLine.= '\''.($concurrentMin).'\',';
			$cmdLine.= '\''.($sessionName).'\',';
			$cmdLine.= '\''.$cmdLineAdjusted.'\');';
                        $cmdLine.= 'if(\$rv==false) exit(1);';
                        $cmdLine.= '"';
		}
		$cmdLine.= " >> ".$this->logFilePath." 2>&1";
		VidiunLog::log("Final cmdLine:$cmdLine");

		$output = system($cmdLine, $returnVar);
		VidiunLog::log("rv($returnVar),".print_r($output,1));
		return $output;
	}

	/**
	 *
	 */
	private function adjust_cmdline($cmdLine)
	{
		VidiunLog::log("Original cmdLine:$cmdLine");
			/*
			 * Clean up the cmd line - remove 'ffmpeg' and log file redirection instructions
			 * those will be handled by the Chunked flow
			 */
		$cmdLineAdjusted = str_replace(VBatchBase::$taskConfig->params->ffmpegCmd, VDLCmdlinePlaceholders::BinaryName, $cmdLine);
		$cmdValsArr = explode(' ', $cmdLineAdjusted);
		if(($idx=array_search('>>', $cmdValsArr))!==false){
			$cmdValsArr = array_slice ($cmdValsArr,0,$idx);
		}
		if(($idx=array_search(VDLCmdlinePlaceholders::BinaryName, $cmdValsArr))!==false){
			unset($cmdValsArr[$idx]);
		}
		if(($idx=array_search('&&', $cmdValsArr))!==false){
			$cmdValsArr[$idx] = "ANDAND";
		}

		foreach($cmdValsArr as $idx=>$val){
			$val = trim($val);
			if(!isset($val) || $val==' ' || $val==""){
				unset($cmdValsArr[$idx]);
			}
		}
		$cmdLineAdjusted = implode(" ",$cmdValsArr);
		$cmdLineAdjusted = str_replace(VDLCmdlinePlaceholders::BinaryName, VBatchBase::$taskConfig->params->ffmpegCmd, $cmdLineAdjusted);
		$cmdLineAdjusted = str_replace('\'', '\\\'',$cmdLineAdjusted);
		VidiunLog::log("Cleaned up cmdLine:$cmdLineAdjusted");
		
		return $cmdLineAdjusted;
	}
}

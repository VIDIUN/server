<?php
/**
 * base class for the real ConversionEngines in the system - ffmpeg,menconder and flix. 
 * 
 * @package Scheduler
 * @subpackage Conversion.engines
 */
abstract class VCollectionConversionEngine extends VConversionEngine
{
	protected abstract function convertCollection ( VidiunConvertCollectionJobData &$data );
	protected abstract function parseCreatedFiles (VidiunConvertCollectionJobData &$data);
	
	public function simulate ( VidiunConvartableJobData $data )
	{
		return $this->simulateCollection ( $data );
	}	
	
	private function simulateCollection ( VidiunConvertCollectionJobData $data )
	{
		return  ''; //TODO
	}
	
	public function convert ( VidiunConvartableJobData &$data )
	{
		return  $this->convertCollection ( $data );
	}	
	
	
	/**
	 * @param VidiunConvertJobData $data
	 * @return array<VConversioEngineResult>
	 */
	protected function getExecutionCommandAndConversionString ( VidiunConvertCollectionJobData $data )
	{
		$uniqid = uniqid("convert_") . '.xml';
		$xmlPath = $data->destDirLocalPath . DIRECTORY_SEPARATOR . $uniqid;
		copy($data->inputXmlLocalPath, $xmlPath);
		$xml = file_get_contents($xmlPath);
		$xml = str_replace(VDLCmdlinePlaceholders::OutDir, $data->destDirLocalPath, $xml);
		file_put_contents($xmlPath, $xml);

		VidiunLog::debug("Config File Path: $xmlPath");
		$this->configFilePath = $xmlPath;
		$this->logFilePath = $data->destDirLocalPath . DIRECTORY_SEPARATOR . $data->destFileName . '.log';
		
				
		VidiunLog::debug("Command Line Str: " . $data->commandLinesStr);
		$cmd_line_arr = $this->getCmdArray($data->commandLinesStr);
		
		$conversion_engine_result_list = array();
		foreach ( $cmd_line_arr as $type => $cmd_line )
		{
			VidiunLog::debug("Command Line type[$type] line[$cmd_line]");
			
			if($type != $this->getType())
				continue;
				
			$cmdArr = explode(self::MILTI_COMMAND_LINE_SEPERATOR, $cmd_line);
			$lastIndex = count($cmdArr) - 1;
			
			foreach($cmdArr as $index => $cmd)
			{
				if($index == 0)
				{	
					$this->inFilePath = $this->getSrcActualPathFromData($data);
				}
				else
				{
					$this->inFilePath = $this->outFilePath;
				}
			
				if($lastIndex > $index)
				{
					$uniqid = uniqid("tmp_convert_");
					$this->outFilePath = $data->destDirLocalPath . DIRECTORY_SEPARATOR . $uniqid;
				}
				else
				{
					$this->outFilePath = $data->destDirLocalPath . DIRECTORY_SEPARATOR . $data->destFileName;	
				}
				
				$cmd = trim($cmd);
				if($cmd == self::FAST_START_SIGN)
				{
					$exec_cmd = $this->getQuickStartCmdLine(true);
				}
				else
				{
					$exec_cmd = $this->getCmdLine ( $cmd , true );
				}
				$conversion_engine_result = new VConversioEngineResult( $exec_cmd , $cmd );
				$conversion_engine_result_list[] = $conversion_engine_result;
			}	
		}
		
		return $conversion_engine_result_list;			
	}	
}



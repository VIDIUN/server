<?php
/**
 * base class for the real ConversionEngines in the system - ffmpeg,menconder and flix. 
 * 
 * @package Scheduler
 * @subpackage Conversion
 */
class VSingleOutputOperationEngine extends VOperationEngine
{
	/**
	 * @var string
	 */
	protected $outFilePath;
	
	protected function getCmdLine()
	{
		if(isset($this->configFilePath)){
			$xml = file_get_contents($this->configFilePath);
			$xml = str_replace(
					array(VDLCmdlinePlaceholders::OutDir,VDLCmdlinePlaceholders::OutFileName), 
					array($this->outDir,$this->outFilePath), 
					$xml);
			file_put_contents($this->configFilePath, $xml);
		}
		
		$command = '';
		if($this->operator && $this->operator->command)
		{
			$command = str_replace ( 
				array(VDLCmdlinePlaceholders::InFileName, VDLCmdlinePlaceholders::OutFileName, VDLCmdlinePlaceholders::ConfigFileName, VDLCmdlinePlaceholders::BinaryName), 
				array($this->inFilePath, $this->outFilePath, $this->configFilePath, $this->cmd),
				$this->operator->command);
		}
				
		return "{$this->cmd} $command >> \"{$this->logFilePath}\" 2>&1";
	}

	public function __construct($cmd, $outFilePath)
	{
		parent::__construct($cmd);
		
		$this->outFilesPath[] = $outFilePath;
		$this->outFilePath = $outFilePath;
		$this->logFilePath = "$outFilePath.log";
	}
}



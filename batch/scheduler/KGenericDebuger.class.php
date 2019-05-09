<?php

/**
 * Will schedual execution of external commands
 * Copied base functionality from VScheduler
 *
 * @package Scheduler
 * @subpackage Debug
 */
class VGenericDebuger
{
	/**
	 * @var bool
	 */
	private $enableDebug = true;
	
	/**
	 * @var VSchedulerConfig
	 */
	private $schedulerConfig = null;
	
	/**
	 * @var string
	 */
	private $configFileName = null;
	
	private $logDir = "/web/vidiun/log";
	private $initOnly = false;
	
	/**
	 * @param string $configFileName
	 */
	public function __construct($configFileName, $initOnly = false)
	{
		$this->debug(__LINE__, "__construct($configFileName)");
		$this->configFileName = $configFileName;
		$this->initOnly = $initOnly;
		$this->loadConfig();
	}
	
	private function loadConfig()
	{
		$this->debug(__LINE__, "loadConfig()");
		if(!is_null($this->schedulerConfig) && !$this->schedulerConfig->reloadRequired())
			return;
		
		$this->schedulerConfig = new VSchedulerConfig($this->configFileName);
		$this->logDir = $this->schedulerConfig->getLogDir();
	}
	
	/**
	 * @param string $jobName
	 */
	public function run($jobName)
	{
		$this->debug(__LINE__, "run($jobName)");
		
		$taskConfigs = $this->schedulerConfig->getTaskConfigList();
		$this->debug(__LINE__, "taskConfigs: " . count($taskConfigs));
		
		foreach($taskConfigs as $taskConfig)
		{
			if($taskConfig->name == $jobName)
			{
				if($this->initOnly)
					$taskConfig->setInitOnly(true);
					
				$this->exeJob($taskConfig);
			}
		}
		
		$this->debug(__LINE__, "-- Done --");
		die();
	}
	
	/**
	 * @param VSchedularTaskConfig $taskConfig
	 */
	private function exeJob(VSchedularTaskConfig $taskConfig)
	{
		$this->debug(__LINE__, "exeJob($taskConfig->name)");
		
		$taskConfig->setTaskIndex(1);
		//$taskConfig->setInitOnly(true);
		
		$instance = new $taskConfig->type($taskConfig);
		$instance->run(); 
		$instance->done();
	}
	
	/**
	 * @param int $line
	 * @param string $text
	 */
	private function debug($line, $text)
	{
		if($this->enableDebug)
			echo "line $line: $text\n";
	}
}

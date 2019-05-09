<?php
/**
 * @package Scheduler
 * @subpackage Debug
 */
chdir(dirname( __FILE__ ) . "/../../");
require_once(__DIR__ . "/../../bootstrap.php");

/**
 * @package Scheduler
 * @subpackage Debug
 */
class VAsyncCaptureThumbTest extends PHPUnit_Framework_TestCase 
{
	const JOB_NAME = 'VAsyncCaptureThumb';
	
	private $outputFolder;
	private $testsConfig;
	
	private static $thumbParamsAttributes = array(
		"cropType",
		"quality",
		"cropX",
		"cropY",
		"cropWidth",
		"cropHeight",
		"videoOffset",
		"width",
		"height",
		"backgroundColor",
	);
		
	public function setUp() 
	{
		parent::setUp();
		
		$config = new Zend_Config_Ini(dirname(__FILE__) . "/VAsyncCaptureThumbTest.ini");
		$testConfig = $config->get('config');
		$this->outputFolder = dirname(__FILE__) . '/' . $testConfig->outputFolder;
		
		$this->testsConfig = $config->get('tests');
	}
	
	public function tearDown() 
	{
		parent::tearDown();
	}
	
	public function test()
	{
		foreach($this->testsConfig as $testName => $config)
		{
			$thumbParamsOutput = new VidiunThumbParamsOutput();
			foreach(self::$thumbParamsAttributes as $attribute)
			{
				if(isset($config->$attribute))
				{
					$thumbParamsOutput->$attribute = $config->$attribute;
					if($attribute == 'backgroundColor' && !is_numeric($thumbParamsOutput->$attribute))
						$thumbParamsOutput->$attribute = hexdec($thumbParamsOutput->$attribute);
				}
			}
				
			$this->doTest($config->source, $thumbParamsOutput, $config->expectedStatus, $testName);
		}
	}
	
	public function doTest($filePath, VidiunThumbParamsOutput $thumbParamsOutput, $expectedStatus, $testName)
	{
		$outputFileName = "$testName.jpg";
		$finalPath = "$this->outputFolder/$outputFileName";
		if(file_exists($finalPath))
			unlink($finalPath);
				
		$iniFile = "batch_config.ini";
		$schedulerConfig = new VSchedulerConfig($iniFile);
	
		$taskConfigs = $schedulerConfig->getTaskConfigList();
		$config = null;
		foreach($taskConfigs as $taskConfig)
		{
			if($taskConfig->name == self::JOB_NAME)
				$config = $taskConfig;
		}
		$this->assertNotNull($config);
		
		$jobs = $this->prepareJobs($filePath, $thumbParamsOutput);
		
		$config->setTaskIndex(1);
		$instance = new $config->type($config);
		$instance->setUnitTest(true);
		$jobs = $instance->run($jobs); 
		$instance->done();
		
		foreach($jobs as $job)
		{
			$this->assertEquals($expectedStatus, $job->status, "test [$testName] expected status [$expectedStatus] actual status [$job->status] with message [$job->message]");
			if($job->status != VidiunBatchJobStatus::FINISHED)
				continue;
				
			$outPath = $job->data->thumbPath;
			$this->assertFileExists($outPath);
				
			rename($outPath, $finalPath);
		}
	}
	
	private function prepareJobs($filePath, VidiunThumbParamsOutput $thumbParamsOutput)
	{
		$data = new VidiunCaptureThumbJobData();
		$data->srcFileSyncLocalPath = $filePath;
		$data->thumbParamsOutput = $thumbParamsOutput;
		
		$job = new VidiunBatchJob();
		$job->id = 1;
		$job->status = VidiunBatchJobStatus::PENDING;
		$job->data = $data;
		
		return array($job);
	}
}


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
class VAsyncImportTest extends PHPUnit_Framework_TestCase
{
	const JOB_NAME = 'VAsyncImport';
	
	public function setUp() 
	{
		parent::setUp();
	}
	
	public function tearDown() 
	{
		parent::tearDown();
	}
	
	public function testGoodUrl()
	{
		$this->doTest('http://viddev.vidiun.com/content/zbale/9spkxiz8m4_100007.mp4', VidiunBatchJobStatus::FINISHED);
	}
	
//	public function testSpecialCharsUrl()
//	{
//		$this->doTest('http://viddev.vidiun.com/content/zbale/trailer_480 ()p.mov', VidiunBatchJobStatus::FINISHED);
//	}
//	
//	public function testSpacedUrl()
//	{
//		$this->doTest(' http://viddev.vidiun.com/content/zbale/9spkxiz8m4_100007.mp4', VidiunBatchJobStatus::FINISHED);
//	}
//	
//	public function testMissingFileUrl()
//	{
//		$this->doTest('http://localhost/api_v3/sample/xxx.avi', VidiunBatchJobStatus::FAILED);
//	}
//	
//	public function testInvalidServerUrl()
//	{
//		$this->doTest('http://xxx', VidiunBatchJobStatus::FAILED);
//	}
//	
//	public function testInvalidUrl()
//	{
//		$this->doTest('xxx', VidiunBatchJobStatus::FAILED);
//	}
//	
//	public function testEmptyUrl()
//	{
//		$this->doTest('', VidiunBatchJobStatus::FAILED);
//	}
	
	public function doTest($value, $expectedStatus)
	{
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
		
		$jobs = $this->prepareJobs($value);
		
		$config->setTaskIndex(1);
		$instance = new $config->type($config);
		$instance->setUnitTest(true);
		$jobs = $instance->run($jobs); 
		$instance->done();
		
		foreach($jobs as $job)
			$this->assertEquals($expectedStatus, $job->status);
	}
	
	private function prepareJobs($value)
	{
		$data = new VidiunImportJobData();
		$data->srcFileUrl = $value;
		
		$job = new VidiunBatchJob();
		$job->id = 1;
		$job->status = VidiunBatchJobStatus::PENDING;
		$job->data = $data;
		
		return array($job);
	}
}

?>
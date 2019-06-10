<?php
/**
 * @package Scheduler
 * @subpackage Debug
 */
chdir(dirname( __FILE__ ) . "/../../");
require_once(dirname( __FILE__ ) . "/../../bootstrap.php");

/**
 * @package Scheduler
 * @subpackage Debug
 */
class VAsyncConvertCloserTest extends PHPUnit_Framework_TestCase 
{
	const JOB_NAME = 'VAsyncConvertCloser';
	
	public function setUp() 
	{
		parent::setUp();
	}
	
	public function tearDown() 
	{
		parent::tearDown();
	}
	
	public function testEncodingCom()
	{
		$engineType = VidiunConversionEngineType::ENCODING_COM;
		$remoteMediaId = '845877';
		$this->doTest($engineType, $remoteMediaId, '', VidiunBatchJobStatus::FINISHED);
	}
	
	private function doTest($engineType, $remoteMediaId, $remoteUrl, $expectedStatus)
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
		
		$jobs = $this->prepareJobs($engineType, $remoteMediaId, $remoteUrl);
		
		$config->setTaskIndex(1);
		$instance = new $config->type($config);
		$instance->setUnitTest(true);
		$jobs = $instance->run($jobs); 
		$instance->done();
		
		foreach($jobs as $job)
			$this->assertEquals($expectedStatus, $job->status);
	}
	
	private function prepareJobs($engineType, $remoteMediaId, $remoteUrl)
	{
		$data = new VidiunConvertJobData();
		$data->remoteMediaId = $remoteMediaId;
		$data->destFileSyncRemoteUrl = $remoteUrl;
		
		$job = new VidiunBatchJob();
		$job->id = 1;
		$job->jobSubType = $engineType;
		$job->status = VidiunBatchJobStatus::ALMOST_DONE;
		$job->data = $data;
		$job->queueTime = time();
		
		return array($job);
	}
}

?>
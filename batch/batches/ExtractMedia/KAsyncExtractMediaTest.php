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
class VAsyncExtractMediaTest extends PHPUnit_Framework_TestCase 
{
	const JOB_NAME = 'VAsyncExtractMedia';
	
	public function setUp() 
	{
		parent::setUp();
	}
	
	public function tearDown() 
	{
		parent::tearDown();
	}
	
	public function testGoodFile()
	{
		$this->doTest(realpath(dirname( __FILE__ ) . '/../../../tests/files/example.avi'), VidiunBatchJobStatus::FINISHED);
	}
	
	public function testSpacedFile()
	{
		$path = realpath(dirname( __FILE__ ) . '/../../../tests/files/example.avi');
		$this->doTest(" $path", VidiunBatchJobStatus::FINISHED);
	}
	
	public function testMissingFile()
	{
		$this->doTest('aaa', VidiunBatchJobStatus::FAILED);
	}
	
	public function testEmptyFile()
	{
		$this->doTest('aaa', VidiunBatchJobStatus::FAILED);
	}
	
	public function testImageFile()
	{
		$this->doTest(realpath('../tests/files/thumb.jpg'), VidiunBatchJobStatus::FAILED);
	}
	
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
		$data = new VidiunExtractMediaJobData();
		$srcFileSyncDescriptor = new VidiunSourceFileSyncDescriptor();
		$srcFileSyncDescriptor->fileSyncLocalPath = $value;
		$data->srcFileSyncs = new VidiunSourceFileSyncDescriptorArray();
		$data->srcFileSyncs[] = $srcFileSyncDescriptor;
		
		$job = new VidiunBatchJob();
		$job->id = 1;
		$job->status = VidiunBatchJobStatus::PENDING;
		$job->data = $data;
		
		return array($job);
	}
}

?>
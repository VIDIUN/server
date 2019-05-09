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
class VAsyncRecalculateCacheTest extends PHPUnit_Framework_TestCase
{
	const JOB_NAME = 'VAsyncRecalculateCache';
	
	public function testMediaEntryFilter()
	{
		$filter = new VidiunMediaEntryFilter();
		// TODO define the filter
		
		$this->doTestCategoryUser($filter, VidiunBatchJobStatus::FINISHED);
	}

	public function doTestCategoryUser(VidiunBaseEntryFilter $filter, $expectedStatus)
	{
		$this->doTest(VidiunCopyObjectType::CATEGORY_USER, $filter, $expectedStatus);
	}
	
	public function doTest($objectType, VidiunFilter $filter, $expectedStatus)
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
		
		$jobs = $this->prepareJobs($objectType, $filter);
		
		$config->setTaskIndex(1);
		$instance = new $config->type($config);
		$instance->setUnitTest(true);
		$jobs = $instance->run($jobs); 
		$instance->done();
		
		foreach($jobs as $job)
			$this->assertEquals($expectedStatus, $job->status);
	}
	
	private function prepareJobs($objectType, VidiunFilter $filter)
	{
		$data = new VidiunCopyJobData();
		$data->filter = $filter;
		
		$job = new VidiunBatchJob();
		$job->id = 1;
		$job->jobSubType = $objectType;
		$job->status = VidiunBatchJobStatus::PENDING;
		$job->data = $data;
		
		return array($job);
	}
}

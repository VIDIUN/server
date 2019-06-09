<?php
/**
 * @package    Core
 * @subpackage system
 * @deprecated
 */
require_once ( __DIR__ . "/vidiunSystemAction.class.php" );

/**
 * @package    Core
 * @subpackage system
 * @deprecated
 */
class restartJobAction extends vidiunSystemAction
{
	/**
	 * Will investigate a single entry
	 */
	public function execute()
	{
		$this->forceSystemAuthentication();

		$batchjob_id = @$_REQUEST["batchjob_id"];
		$entry_id = @$_REQUEST["entry_id"];
		
		$job = BatchJobPeer::retrieveByPK($batchjob_id);
		if ($job)
		{
			$job->setStatus(BatchJob::BATCHJOB_STATUS_PENDING);
			$job->save();
		}
		
		$this->redirect ( "/system/investigate?entry_id=$entry_id" );
	}
}
?>